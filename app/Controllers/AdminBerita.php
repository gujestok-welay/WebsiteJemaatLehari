<?php

namespace App\Controllers;

use App\Models\BeritaModel;
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
        $current = $id > 0 ? $this->model->find($id) : null;
        if ($id > 0 && ! $current) {
            return redirect()->to(site_url($returnTo))->with('error', 'Berita tidak ditemukan.');
        }

        $postedFields = [];
        foreach (['title', 'summary', 'content', 'status', 'slug'] as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null && ! is_string($value)) {
                return $this->redirectWithSaveError('Data berita tidak valid. Periksa kembali isian formulir.', $id, $returnTo);
            }
            $postedFields[$field] = $value ?? '';
        }

        $title = trim($postedFields['title']);
        $summary = trim($postedFields['summary']);
        $content = trim($postedFields['content']);
        $status = $postedFields['status'];
        $slug = trim($postedFields['slug']);

        if ($title === '' || $summary === '' || $content === '' || ! in_array($status, BeritaModel::statusOptions(), true)) {
            return $this->redirectWithSaveError('Judul, ringkasan, isi, dan status berita wajib diisi.', $id, $returnTo);
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
        }

        try {
            if ($id > 0) {
                $saved = $this->model->update($id, $data);
            } else {
                $data['created_by'] = (int) session()->get('admin_id');
                $saved = $this->model->insert($data);
            }
        } catch (DatabaseException $exception) {
            if ($newCover !== null) {
                $this->removeStoredCover($newCover);
            }
            log_message('error', 'Gagal menyimpan berita: {message}', ['message' => $exception->getMessage()]);

            return $this->redirectWithSaveError('Berita gagal disimpan. Silakan coba lagi.', $id, $returnTo);
        }

        if ($saved === false) {
            if ($newCover !== null) {
                $this->removeStoredCover($newCover);
            }

            $errors = array_filter($this->model->errors());
            $message = $errors === []
                ? 'Berita gagal disimpan. Silakan periksa kembali data dan coba lagi.'
                : implode(' ', $errors);

            return $this->redirectWithSaveError($message, $id, $returnTo);
        }

        if ($id > 0 && $newCover !== null && ! empty($current['cover_image'])) {
            $this->removeStoredCover((string) $current['cover_image']);
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

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita tidak ditemukan.');
        }

        $this->model->delete($id);
        return redirect()->to(site_url('admin/berita'))->with('success', 'Berita diarsipkan atau dihapus dari daftar aktif.');
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
}
