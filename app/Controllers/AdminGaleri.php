<?php

namespace App\Controllers;

use App\Models\KegiatanMediaModel;
use App\Models\KegiatanModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use RuntimeException;

class AdminGaleri extends BaseController
{
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;
    private const MAX_PHOTOS_PER_ACTIVITY = 30;

    protected KegiatanModel $model;
    protected KegiatanMediaModel $mediaModel;

    public function __construct()
    {
        $this->model = new KegiatanModel();
        $this->mediaModel = new KegiatanMediaModel();
    }

    public function index(): string
    {
        $term = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');

        return $this->renderAdminPage('admin/galeri/index', [
            'items' => $this->model->findByTerm($term, $status !== '' ? $status : null),
            'term' => $term,
            'status' => $status,
            'statusOptions' => KegiatanModel::statusOptions(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ], 'Galeri kegiatan');
    }

    public function create(): string
    {
        return $this->renderAdminPage('admin/galeri/form', [
            'item' => null,
            'media' => [],
            'posterTypeOptions' => KegiatanModel::posterTypeOptions(),
            'statusOptions' => KegiatanModel::formStatusOptions(),
            'error' => session()->getFlashdata('error'),
        ], 'Tambah kegiatan');
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! is_array($item) || ($item['status'] ?? '') === 'Arsip') {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'Kegiatan tidak ditemukan atau sudah diarsipkan.');
        }

        return $this->renderAdminPage('admin/galeri/form', [
            'item' => $item,
            'media' => $this->mediaModel->findByKegiatan($id),
            'posterTypeOptions' => KegiatanModel::posterTypeOptions(),
            'statusOptions' => KegiatanModel::formStatusOptions(),
            'error' => session()->getFlashdata('error'),
        ], 'Ubah kegiatan');
    }

    public function media(string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        if (preg_match('~\A[A-Za-z0-9._-]+\.(?:jpe?g|png|webp)\z~i', $filename) !== 1) {
            return $this->response->setStatusCode(404);
        }

        $storedPath = 'uploads/galeri/' . $filename;
        $coverExists = db_connect()->table('kegiatan')->where('poster_path', $storedPath)->countAllResults() > 0;
        $mediaExists = $coverExists || $this->mediaModel->where('file_path', $storedPath)->countAllResults() > 0;
        if (! $mediaExists) {
            return $this->response->setStatusCode(404);
        }

        return $this->readStoredImage($filename);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $idValue = $this->request->getPost('id');
        if ($idValue !== null && ! is_string($idValue) && ! is_int($idValue)) {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'ID kegiatan tidak valid.');
        }

        $rawId = trim((string) ($idValue ?? ''));
        if ($rawId !== '' && (! ctype_digit($rawId) || (int) $rawId < 1)) {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'ID kegiatan tidak valid.');
        }

        $id = $rawId === '' ? 0 : (int) $rawId;
        $current = $id > 0 ? $this->model->find($id) : null;
        if ($id > 0 && ! is_array($current)) {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'Kegiatan tidak ditemukan.');
        }
        if ($id > 0 && ($current['status'] ?? '') === 'Arsip') {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'Pulihkan kegiatan terlebih dahulu sebelum mengubahnya.');
        }

        $fields = [];
        foreach (['title', 'description', 'event_date', 'video_url', 'poster_type', 'status'] as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null && ! is_string($value)) {
                return $this->saveError('Data kegiatan tidak valid. Periksa kembali isian formulir.', $id);
            }
            $fields[$field] = trim((string) ($value ?? ''));
        }

        $title = $fields['title'];
        $description = $fields['description'];
        $eventDate = $fields['event_date'];
        $videoUrl = $fields['video_url'];
        $posterType = $fields['poster_type'];
        $status = $fields['status'];
        $showOnHome = $this->request->getPost('show_on_home') === '1' ? 1 : 0;

        if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
            return $this->saveError('Judul kegiatan wajib diisi (3–255 karakter).', $id);
        }
        if (mb_strlen($description) > 5000) {
            return $this->saveError('Keterangan kegiatan maksimal 5.000 karakter.', $id);
        }
        if (! in_array($posterType, array_keys(KegiatanModel::posterTypeOptions()), true)) {
            return $this->saveError('Jenis poster tidak valid.', $id);
        }
        if (! in_array($status, KegiatanModel::formStatusOptions(), true)) {
            return $this->saveError('Status publikasi tidak valid.', $id);
        }
        if ($eventDate !== '' && ! $this->isValidDate($eventDate)) {
            return $this->saveError('Tanggal kegiatan tidak valid.', $id);
        }
        if ($videoUrl !== '' && ! $this->isSafeExternalUrl($videoUrl)) {
            return $this->saveError('Tautan video harus berupa URL lengkap yang diawali http:// atau https://.', $id);
        }

        $removeIds = $this->postedMediaIds();
        if ($removeIds === null) {
            return $this->saveError('Pilihan foto yang dihapus tidak valid.', $id);
        }
        $existingMedia = $id > 0 ? $this->mediaModel->findByKegiatan($id) : [];
        $existingById = [];
        foreach ($existingMedia as $media) {
            $existingById[(int) $media['id']] = $media;
        }
        foreach ($removeIds as $removeId) {
            if (! isset($existingById[$removeId])) {
                return $this->saveError('Pilihan foto yang dihapus tidak cocok dengan kegiatan ini.', $id);
            }
        }
        $removedMedia = array_intersect_key($existingById, array_flip($removeIds));
        $remainingCount = count($existingMedia) - count($removedMedia);

        $poster = $this->request->getFile('poster');
        $hasNewPoster = $poster instanceof UploadedFile && $poster->getError() !== UPLOAD_ERR_NO_FILE;
        if (! $hasNewPoster && empty($current['poster_path'])) {
            return $this->saveError('Poster atau flayer utama wajib diunggah.', $id);
        }

        $photoFiles = $this->request->getFileMultiple('photos');
        $photoFiles = is_array($photoFiles) ? array_values(array_filter(
            $photoFiles,
            static fn(mixed $file): bool => $file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE
        )) : [];
        if ($remainingCount + count($photoFiles) > self::MAX_PHOTOS_PER_ACTIVITY) {
            return $this->saveError('Maksimal 30 foto dokumentasi untuk setiap kegiatan.', $id);
        }

        $filesToUpload = [];
        if ($hasNewPoster) {
            $filesToUpload[] = $poster;
        }
        foreach ($photoFiles as $photo) {
            $filesToUpload[] = $photo;
        }
        foreach ($filesToUpload as $file) {
            $validation = $this->validateImage($file);
            if ($validation !== null) {
                return $this->saveError($validation, $id);
            }
        }

        $storedUploads = [];
        foreach ($filesToUpload as $file) {
            $result = $this->storeImage($file);
            if ($result['success'] === false) {
                $this->removeStoredFiles($storedUploads);

                return $this->saveError($result['message'], $id);
            }
            $storedUploads[] = [
                'file' => $result['file'],
                'media_type' => $file === $poster ? $posterType : 'foto',
                'is_poster' => $file === $poster,
            ];
        }

        $posterUpload = null;
        $photoUploads = [];
        foreach ($storedUploads as $upload) {
            if ($upload['is_poster']) {
                $posterUpload = $upload['file'];
            } else {
                $photoUploads[] = $upload;
            }
        }

        $data = [
            'title' => $title,
            'slug' => $this->model->generateSlug($title, $id),
            'description' => $description !== '' ? $description : null,
            'event_date' => $eventDate !== '' ? $eventDate : null,
            'video_url' => $videoUrl !== '' ? $videoUrl : null,
            'poster_path' => $posterUpload ?? $current['poster_path'],
            'poster_type' => $posterType,
            'status' => $status,
            'show_on_home' => $showOnHome,
            'updated_by' => (int) session()->get('admin_id'),
        ];

        $db = db_connect();
        $db->transBegin();

        try {
            if ($id > 0) {
                $saved = $this->model->update($id, $data);
                $activityId = $id;
            } else {
                $data['created_by'] = (int) session()->get('admin_id');
                $activityId = (int) $this->model->insert($data);
                $saved = $activityId > 0;
            }

            if ($saved === false) {
                throw new RuntimeException($this->model->errors() === []
                    ? 'Kegiatan gagal disimpan.'
                    : implode(' ', $this->model->errors()));
            }

            foreach ($removedMedia as $media) {
                $this->mediaModel->delete((int) $media['id']);
            }
            foreach ($photoUploads as $upload) {
                if (! $this->mediaModel->insert([
                    'kegiatan_id' => $activityId,
                    'media_type' => $upload['media_type'],
                    'file_path' => $upload['file'],
                ])) {
                    throw new RuntimeException('Foto dokumentasi gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Perubahan kegiatan gagal disimpan ke basis data.');
            }
            $db->transCommit();
        } catch (DatabaseException $exception) {
            $db->transRollback();
            $this->removeStoredFiles($storedUploads);
            log_message('error', 'Gagal menyimpan kegiatan: {message}', ['message' => $exception->getMessage()]);

            return $this->saveError('Kegiatan gagal disimpan. Silakan coba lagi.', $id);
        } catch (RuntimeException $exception) {
            $db->transRollback();
            $this->removeStoredFiles($storedUploads);

            return $this->saveError($exception->getMessage(), $id);
        }

        if ($posterUpload !== null && ! empty($current['poster_path'])) {
            $this->removeStoredFile((string) $current['poster_path']);
        }
        foreach ($removedMedia as $media) {
            $this->removeStoredFile((string) $media['file_path']);
        }

        return redirect()->to(site_url('admin/galeri'))->with(
            'success',
            $id > 0 ? 'Kegiatan berhasil diperbarui.' : 'Kegiatan baru berhasil disimpan.'
        );
    }

    public function archive(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! is_array($item)) {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'Kegiatan tidak ditemukan.');
        }
        if (($item['status'] ?? '') === 'Arsip') {
            return redirect()->to(site_url('admin/galeri'))->with('error', 'Kegiatan sudah diarsipkan.');
        }

        try {
            $saved = $this->model->update($id, [
                'status' => 'Arsip',
                'archived_from_status' => in_array($item['status'] ?? '', KegiatanModel::formStatusOptions(), true)
                    ? $item['status']
                    : 'Draft',
                'updated_by' => (int) session()->get('admin_id'),
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal mengarsipkan kegiatan ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/galeri'))->with('error', 'Kegiatan gagal diarsipkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/galeri'))->with(
            $saved ? 'success' : 'error',
            $saved ? 'Kegiatan berhasil diarsipkan.' : 'Kegiatan gagal diarsipkan. Silakan coba lagi.'
        );
    }

    public function restore(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! is_array($item) || ($item['status'] ?? '') !== 'Arsip') {
            return redirect()->to(site_url('admin/galeri?status=Arsip'))->with('error', 'Kegiatan arsip tidak ditemukan.');
        }

        $previousStatus = in_array($item['archived_from_status'] ?? '', KegiatanModel::formStatusOptions(), true)
            ? $item['archived_from_status']
            : 'Draft';

        try {
            $saved = $this->model->update($id, [
                'status' => $previousStatus,
                'archived_from_status' => null,
                'updated_by' => (int) session()->get('admin_id'),
            ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal memulihkan kegiatan ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/galeri?status=Arsip'))->with('error', 'Kegiatan gagal dipulihkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/galeri?status=Arsip'))->with(
            $saved ? 'success' : 'error',
            $saved ? 'Kegiatan berhasil dipulihkan.' : 'Kegiatan gagal dipulihkan. Silakan coba lagi.'
        );
    }

    private function saveError(string $message, int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $url = $id > 0 ? site_url('admin/galeri/edit/' . $id) : site_url('admin/galeri/create');

        return redirect()->to($url)->withInput()->with('error', $message);
    }

    private function isValidDate(string $date): bool
    {
        $value = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        return $value !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $value->format('Y-m-d') === $date;
    }

    private function isSafeExternalUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function postedMediaIds(): ?array
    {
        $values = $this->request->getPost('remove_media');
        if ($values === null) {
            return [];
        }
        if (! is_array($values)) {
            return null;
        }

        $ids = [];
        foreach ($values as $value) {
            if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value) || (int) $value < 1) {
                return null;
            }
            $ids[] = (int) $value;
        }

        return array_values(array_unique($ids));
    }

    private function validateImage(UploadedFile $file): ?string
    {
        if (in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return 'Ukuran setiap gambar maksimal 10 MB.';
        }
        if (! $file->isValid()) {
            return 'File gambar tidak valid.';
        }
        if (
            ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)
            || ! in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)
        ) {
            return 'Jenis gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        }

        $size = $file->getSize();
        if ($size === false || $size > self::MAX_IMAGE_BYTES) {
            return 'Ukuran setiap gambar maksimal 10 MB.';
        }

        return null;
    }

    private function storeImage(UploadedFile $file): array
    {
        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'galeri';
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            return ['success' => false, 'message' => 'Direktori penyimpanan galeri tidak dapat dibuat.'];
        }

        $safeName = $file->getRandomName();
        try {
            $file->move($targetDir, $safeName);
        } catch (HTTPException $exception) {
            log_message('error', 'Gagal menyimpan gambar galeri: {message}', ['message' => $exception->getMessage()]);

            return ['success' => false, 'message' => 'Gambar gagal disimpan. Silakan coba lagi.'];
        }

        if (! is_file($targetDir . DIRECTORY_SEPARATOR . $safeName)) {
            return ['success' => false, 'message' => 'Gambar gagal disimpan. Silakan coba lagi.'];
        }

        return ['success' => true, 'file' => 'uploads/galeri/' . basename($safeName)];
    }

    private function removeStoredFiles(array $files): void
    {
        foreach ($files as $file) {
            $path = is_array($file) ? (string) ($file['file'] ?? '') : (string) $file;
            if ($path !== '') {
                $this->removeStoredFile($path);
            }
        }
    }

    private function removeStoredFile(string $storedPath): void
    {
        if (! preg_match('~\Auploads/galeri/([A-Za-z0-9._-]+\.(?:jpe?g|png|webp))\z~i', $storedPath, $matches)) {
            return;
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'galeri' . DIRECTORY_SEPARATOR . $matches[1];
        if (is_file($path) && ! unlink($path)) {
            log_message('warning', 'Gambar galeri tidak dapat dihapus: {filename}', ['filename' => $matches[1]]);
        }
    }

    private function readStoredImage(string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'galeri' . DIRECTORY_SEPARATOR . $filename;
        if (! is_file($filePath) || ! is_readable($filePath)) {
            log_message('error', 'Gambar galeri admin tidak tersedia: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            log_message('error', 'Tipe gambar galeri admin tidak didukung: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(404);
        }

        $image = @file_get_contents($filePath);
        if ($image === false) {
            log_message('error', 'Gagal membaca gambar galeri admin: {filename}', ['filename' => $filename]);

            return $this->response->setStatusCode(500);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($image);
    }
}
