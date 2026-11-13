<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use App\Libraries\ExternalNewsMetadata;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;

class AdminBerita extends BaseController
{
    private const MAX_COVER_IMAGE_BYTES = 10 * 1024 * 1024;

    protected BeritaModel $model;

    public function __construct()
    {
        $this->model = new BeritaModel();
    }

    public function index(): string
    {
        $term = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $newsError = session()->getFlashdata('news_error') ?? '';
        $items = $this->model->findByTerm($term, $status ?: null);

        return $this->renderAdminPage('admin/berita/index', [
            'items' => $items,
            'term' => $term,
            'status' => $status,
            'statusOptions' => BeritaModel::statusOptions(),
            'formStatusOptions' => BeritaModel::formStatusOptions(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'newsError' => $newsError,
            'newsModalOpen' => $this->request->getGet('create') === '1'
                || $newsError !== '',
        ], 'Berita');
    }

    public function create(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(site_url('admin/berita?create=1'));
    }

    public function metadata(): \CodeIgniter\HTTP\ResponseInterface
    {
        $url = $this->request->getPost('url');
        if (! is_string($url)) {
            return $this->metadataJson(['message' => 'Masukkan tautan berita yang valid.'], 422);
        }

        try {
            $metadata = (new ExternalNewsMetadata())->fetch($url);
        } catch (\InvalidArgumentException $exception) {
            return $this->metadataJson(['message' => $exception->getMessage()], 422);
        } catch (\RuntimeException $exception) {
            log_message('warning', 'Metadata berita eksternal tidak dapat diambil: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->metadataJson(['message' => $exception->getMessage()], 422);
        }

        return $this->metadataJson([
            'title' => $metadata['title'],
            'source_name' => $metadata['source_name'],
            'summary' => $metadata['summary'],
            'image_url' => $metadata['image_url'],
            'image_available' => $metadata['image_url'] !== null,
        ]);
    }

    private function metadataJson(array $data, int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data + [
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash(),
            ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $idValue = $this->request->getPost('id');
        if ($idValue !== null && ! is_string($idValue) && ! is_int($idValue)) {
            return redirect()->to(site_url($this->getReturnTo()))->with('error', 'ID berita tidak valid.');
        }

        $rawId = trim((string) ($idValue ?? ''));
        $returnTo = $this->getReturnTo();
        if ($rawId !== '' && (! ctype_digit($rawId) || (int) $rawId < 1)) {
            return redirect()->to(site_url($returnTo))->with('error', 'ID berita tidak valid.');
        }

        $id = $rawId === '' ? 0 : (int) $rawId;
        $current = null;
        if ($id > 0) {
            $lookup = $this->model->findForAdmin($id);
            if ($lookup['status'] === 'error') {
                return redirect()->to(site_url($returnTo))->with('error', 'Berita gagal dibaca. Silakan coba lagi.');
            }
            if ($lookup['status'] === 'not_found') {
                return redirect()->to(site_url($returnTo))->with('error', 'Berita tidak ditemukan.');
            }

            $current = $lookup['record'];
        }
        if ($id > 0 && ($current['status'] ?? null) === 'Arsip') {
            return redirect()->to(site_url($returnTo))->with('error', 'Pulihkan berita terlebih dahulu sebelum mengubahnya.');
        }

        $postedFields = [];
        foreach (['title', 'summary', 'content', 'status', 'slug', 'article_type', 'source_url', 'source_name', 'source_image_url'] as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null && ! is_string($value)) {
                return $this->redirectWithSaveError('Data berita tidak valid. Periksa kembali isian formulir.', $id, $returnTo);
            }
            $postedFields[$field] = $value ?? '';
        }
        $useSourceImageValue = $this->request->getPost('use_source_image');
        if ($useSourceImageValue !== null && ! is_string($useSourceImageValue)) {
            return $this->redirectWithSaveError('Pilihan gambar kover tidak valid.', $id, $returnTo);
        }

        $title = trim($postedFields['title']);
        $summary = trim($postedFields['summary']);
        $content = trim($postedFields['content']);
        $status = $postedFields['status'];
        $slug = trim($postedFields['slug']);
        $articleType = trim($postedFields['article_type']) ?: 'internal';
        $sourceUrl = trim($postedFields['source_url']);
        $sourceName = trim($postedFields['source_name']);
        $sourceImageUrl = trim($postedFields['source_image_url']);
        $useSourceImage = $useSourceImageValue === '1';

        if (
            $title === ''
            || $summary === ''
            || ($articleType === 'internal' && $content === '')
            || ! in_array($status, BeritaModel::formStatusOptions(), true)
        ) {
            return $this->redirectWithSaveError('Judul, ringkasan, isi, dan status berita wajib diisi.', $id, $returnTo);
        }
        if (! in_array($articleType, ['internal', 'external'], true)) {
            return $this->redirectWithSaveError('Jenis berita tidak valid.', $id, $returnTo);
        }

        if ($articleType === 'external') {
            try {
                $sourceUrl = (new ExternalNewsMetadata())->normalizeUrl($sourceUrl);
            } catch (\InvalidArgumentException $exception) {
                return $this->redirectWithSaveError($exception->getMessage(), $id, $returnTo);
            }

            if ($sourceName === '' || mb_strlen($sourceName) > 255) {
                return $this->redirectWithSaveError('Nama sumber wajib diisi dan maksimal 255 karakter.', $id, $returnTo);
            }
            if ($useSourceImage) {
                try {
                    $sourceImageUrl = (new ExternalNewsMetadata())->normalizeUrl($sourceImageUrl);
                } catch (\InvalidArgumentException $exception) {
                    return $this->redirectWithSaveError('Gambar sumber tidak valid. Ambil ulang metadata atau pilih gambar kover manual.', $id, $returnTo);
                }
            } else {
                $sourceImageUrl = '';
            }
            $content = $summary;
        } else {
            if ($content === '') {
                return $this->redirectWithSaveError('Isi berita wajib diisi.', $id, $returnTo);
            }

            $sourceUrl = '';
            $sourceName = '';
            $sourceImageUrl = '';
            $useSourceImage = false;
        }

        $slug = $slug !== '' ? $slug : BeritaModel::generateSlug($title);
        $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower($slug)) ?? $slug;
        $slug = preg_replace('/-+/', '-', trim($slug, '-')) ?? $slug;

        $data = [
            'title' => $title,
            'slug' => $slug,
            'summary' => $summary,
            'content' => $content,
            'status' => $status,
            'article_type' => $articleType,
            'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
            'source_name' => $sourceName !== '' ? $sourceName : null,
            'updated_by' => (int) session()->get('admin_id'),
        ];

        $uploadedFile = $this->request->getFile('cover_image');
        $newCover = null;
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleCoverUpload($uploadedFile);
            if ($uploadResult['success'] === false) {
                return $this->redirectWithSaveError($uploadResult['message'], $id, $returnTo);
            }
            $newCover = $uploadResult['file'];
            $data['cover_image'] = $newCover;
        } elseif ($useSourceImage) {
            try {
                $uploadResult = $this->handleExternalCover($sourceImageUrl);
            } catch (\InvalidArgumentException|\RuntimeException $exception) {
                log_message('warning', 'Gambar kover berita eksternal tidak dapat diambil: {message}', [
                    'message' => $exception->getMessage(),
                ]);

                return $this->redirectWithSaveError($exception->getMessage(), $id, $returnTo);
            }

            if ($uploadResult['success'] === false) {
                return $this->redirectWithSaveError($uploadResult['message'], $id, $returnTo);
            }
            $newCover = $uploadResult['file'];
            $data['cover_image'] = $newCover;
        }

        try {
            if ($id > 0) {
                $data['id'] = $id;
                $saved = $this->model->update($id, $data);
            } else {
                $data['created_by'] = (int) session()->get('admin_id');
                $saved = $this->model->insert($data);
            }
        } catch (DatabaseException $exception) {
            if ($newCover !== null) {
                $this->removeStoredCoverSafely($newCover);
            }
            log_message('error', 'Gagal menyimpan berita: {message}', ['message' => $exception->getMessage()]);

            return $this->redirectWithSaveError('Berita gagal disimpan. Silakan coba lagi.', $id, $returnTo);
        }

        if ($saved === false) {
            if ($newCover !== null) {
                $this->removeStoredCoverSafely($newCover);
            }

            $errors = array_filter($this->model->errors());
            $message = $errors === []
                ? 'Berita gagal disimpan. Silakan periksa kembali data dan coba lagi.'
                : implode(' ', $errors);

            return $this->redirectWithSaveError($message, $id, $returnTo);
        }

        if ($id > 0 && $newCover !== null && ! empty($current['cover_image'])) {
            $this->removeStoredCoverSafely((string) $current['cover_image']);
        }

        $successMessage = $id > 0 ? 'Berita berhasil diperbarui.' : 'Berita baru berhasil disimpan.';

        return redirect()->to(site_url($returnTo))->with('success', $successMessage);
    }

    protected function redirectWithSaveError(string $message, int $id, string $returnTo): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($id > 0) {
            return redirect()
                ->to(site_url($returnTo) . '?edit=' . $id)
                ->withInput()
                ->with('news_error', $message);
        }

        return redirect()->to(site_url($returnTo) . '?create=1')->withInput()->with('news_error', $message);
    }

    protected function getReturnTo(): string
    {
        $returnTo = $this->request->getPost('return_to');

        return is_string($returnTo) && in_array($returnTo, ['admin/berita', 'admin/dashboard'], true)
            ? $returnTo
            : 'admin/berita';
    }

    public function archive(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $lookup = $this->model->findForAdmin($id);
        if ($lookup['status'] === 'error') {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita gagal diarsipkan. Silakan coba lagi.');
        }
        if ($lookup['status'] === 'not_found') {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita tidak ditemukan.');
        }
        $item = $lookup['record'];
        if (($item['status'] ?? null) === 'Arsip') {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita sudah diarsipkan.');
        }

        try {
            $saved = $this->model->update($id, [
                'status' => 'Arsip',
                'archived_from_status' => in_array($item['status'] ?? null, BeritaModel::formStatusOptions(), true)
                    ? $item['status']
                    : 'Draft',
                'updated_by' => (int) session()->get('admin_id'),
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal mengarsipkan berita ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita gagal diarsipkan. Silakan coba lagi.');
        }

        if ($saved === false) {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita gagal diarsipkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/berita'))->with('success', 'Berita berhasil diarsipkan.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        return $this->archive($id);
    }

    public function restore(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $lookup = $this->model->findForAdmin($id);
        if ($lookup['status'] === 'error') {
            return redirect()->to(site_url('admin/berita?status=Arsip'))->with('error', 'Berita gagal dibaca. Silakan coba lagi.');
        }
        if ($lookup['status'] === 'not_found') {
            return redirect()->to(site_url('admin/berita?status=Arsip'))->with('error', 'Berita tidak ditemukan.');
        }
        $item = $lookup['record'];
        if (($item['status'] ?? null) !== 'Arsip') {
            return redirect()->to(site_url('admin/berita?status=Arsip'))->with('error', 'Berita ini tidak berstatus Arsip.');
        }

        $previousStatus = $item['archived_from_status'] ?? null;
        if (! in_array($previousStatus, BeritaModel::formStatusOptions(), true)) {
            $previousStatus = 'Draft';
        }

        try {
            $saved = $this->model->update($id, [
                'status' => $previousStatus,
                'archived_from_status' => null,
                'updated_by' => (int) session()->get('admin_id'),
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal memulihkan berita ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/berita?status=Arsip'))->with('error', 'Berita gagal dipulihkan. Silakan coba lagi.');
        }

        if ($saved === false) {
            return redirect()->to(site_url('admin/berita?status=Arsip'))->with('error', 'Berita gagal dipulihkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/berita?status=Arsip'))
            ->with('success', 'Berita berhasil dipulihkan ke status ' . $previousStatus . '.');
    }

    protected function handleCoverUpload(UploadedFile $file): array
    {
        if (in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return ['success' => false, 'message' => 'Ukuran gambar maksimal 10 MB.'];
        }

        if (! $file->isValid()) {
            return ['success' => false, 'message' => 'File gambar tidak valid.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getExtension());

        if (! in_array($file->getMimeType(), $allowedMime, true) || ! in_array($ext, $allowedExt, true)) {
            return ['success' => false, 'message' => 'Ekstensi gambar tidak diizinkan. Gunakan JPG, PNG, atau WEBP.'];
        }

        $fileSize = $file->getSize();
        if ($fileSize === false || $fileSize > self::MAX_COVER_IMAGE_BYTES) {
            return ['success' => false, 'message' => 'Ukuran gambar maksimal 10 MB.'];
        }

        $targetDir = WRITEPATH . 'uploads/berita';
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            return ['success' => false, 'message' => 'Direktori upload berita tidak dapat dibuat.'];
        }

        $safeName = $file->getRandomName();
        try {
            $moved = $file->move($targetDir, $safeName);
        } catch (HTTPException $exception) {
            log_message('error', 'Gagal memindahkan gambar berita: {message}', ['message' => $exception->getMessage()]);

            return ['success' => false, 'message' => 'Gambar gagal dipindahkan ke server. Silakan coba lagi.'];
        }

        if (! $moved) {
            return ['success' => false, 'message' => 'Gambar gagal dipindahkan ke server.'];
        }

        $storedPath = 'uploads/berita/' . basename($safeName);

        return ['success' => true, 'file' => $storedPath];
    }

    /**
     * @return array{contents: string, mime: string}
     */
    protected function downloadExternalImage(string $url): array
    {
        return (new ExternalNewsMetadata())->fetchImage($url);
    }

    protected function handleExternalCover(string $url): array
    {
        $image = $this->downloadExternalImage($url);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $contents = $image['contents'] ?? null;
        $mime = $image['mime'] ?? null;
        $imageInfo = is_string($contents) ? @getimagesizefromstring($contents) : false;

        if (
            ! is_string($contents)
            || strlen($contents) > self::MAX_COVER_IMAGE_BYTES
            || ! is_string($mime)
            || ! isset($extensions[$mime])
            || $imageInfo === false
            || ($imageInfo['mime'] ?? null) !== $mime
            || ($imageInfo[0] ?? 0) < 1
            || ($imageInfo[1] ?? 0) < 1
            || $imageInfo[0] * $imageInfo[1] > 40_000_000
        ) {
            return ['success' => false, 'message' => 'Gambar dari sumber tidak valid atau melebihi 10 MB. Pilih gambar kover manual.'];
        }

        $targetDir = WRITEPATH . 'uploads/berita';
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            return ['success' => false, 'message' => 'Direktori upload berita tidak dapat dibuat.'];
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $safeName;
        $written = @file_put_contents($filePath, $contents, LOCK_EX);
        if ($written !== strlen($contents)) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }

            return ['success' => false, 'message' => 'Gambar kover dari sumber gagal disimpan.'];
        }

        return ['success' => true, 'file' => 'uploads/berita/' . $safeName];
    }

    protected function removeStoredCover(string $storedPath): void
    {
        if (! preg_match('~\Auploads/berita/([A-Za-z0-9._-]+)\z~', $storedPath, $matches)) {
            return;
        }

        $filePath = WRITEPATH . 'uploads/berita/' . $matches[1];
        if (is_file($filePath) && ! unlink($filePath)) {
            log_message('warning', 'Gambar berita lama tidak dapat dihapus: {path}', ['path' => $filePath]);
        }
    }

    protected function removeStoredCoverSafely(string $storedPath): void
    {
        try {
            $this->removeStoredCover($storedPath);
        } catch (\ErrorException $exception) {
            log_message('warning', 'Gambar berita tidak dapat dibersihkan: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
