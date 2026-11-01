<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class AdminBerita extends BaseController
{
    protected BeritaModel $model;

    public function __construct()
    {
        $this->model = new BeritaModel();
    }

    public function index(): string
    {
        $term = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $items = $this->model->findByTerm($term, $status ?: null);

        return view('admin/berita/index', [
            'items' => $items,
            'term' => $term,
            'status' => $status,
            'statusOptions' => BeritaModel::statusOptions(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create(): string
    {
        return view('admin/berita/form', [
            'item' => null,
            'statusOptions' => BeritaModel::statusOptions(),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function edit(int $id): string
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/berita'))->with('error', 'Berita tidak ditemukan.');
        }

        return view('admin/berita/form', [
            'item' => $item,
            'statusOptions' => BeritaModel::statusOptions(),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $title = trim((string) $this->request->getPost('title'));
        $summary = trim((string) $this->request->getPost('summary'));
        $content = trim((string) $this->request->getPost('content'));
        $status = (string) $this->request->getPost('status');
        $slug = trim((string) $this->request->getPost('slug'));

        if ($title === '' || $summary === '' || $content === '' || ! in_array($status, BeritaModel::statusOptions(), true)) {
            return redirect()->back()->withInput()->with('error', 'Judul, ringkasan, isi, dan status berita wajib diisi.');
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
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleCoverUpload($uploadedFile, $id ? $id : null);
            if ($uploadResult['success'] === false) {
                return redirect()->back()->withInput()->with('error', $uploadResult['message']);
            }
            $data['cover_image'] = $uploadResult['file'];
        }

        if ($id > 0) {
            $data['updated_by'] = (int) session()->get('admin_id');
            $this->model->update($id, $data);
            return redirect()->to(site_url('admin/berita'))->with('success', 'Berita berhasil diperbarui.');
        }

        $data['created_by'] = (int) session()->get('admin_id');
        $this->model->insert($data);

        return redirect()->to(site_url('admin/berita'))->with('success', 'Berita baru berhasil disimpan.');
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

    protected function handleCoverUpload(UploadedFile $file, ?int $id = null): array
    {
        if (! $file->isValid()) {
            return ['success' => false, 'message' => 'File gambar tidak valid.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getExtension());

        if (! in_array($file->getMimeType(), $allowedMime, true) || ! in_array($ext, $allowedExt, true)) {
            return ['success' => false, 'message' => 'Ekstensi gambar tidak diizinkan. Gunakan JPG, PNG, atau WEBP.'];
        }

        if ((int) ceil($file->getSize() / 1024) > 2048) {
            return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB.'];
        }

        $targetDir = WRITEPATH . 'uploads/berita';
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            return ['success' => false, 'message' => 'Direktori upload berita tidak dapat dibuat.'];
        }

        $safeName = $file->getRandomName();
        if (! $file->move($targetDir, $safeName)) {
            return ['success' => false, 'message' => 'Gambar gagal dipindahkan ke server.'];
        }

        $storedPath = 'uploads/berita/' . basename($safeName);
        if ($id) {
            $current = $this->model->find($id);
            if (! empty($current['cover_image']) && file_exists(FCPATH . $current['cover_image'])) {
                @unlink(FCPATH . $current['cover_image']);
            }
        }

        return ['success' => true, 'file' => $storedPath];
    }
}
