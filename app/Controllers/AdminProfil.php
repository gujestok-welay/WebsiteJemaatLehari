<?php

namespace App\Controllers;

use App\Models\MajelisAnggotaModel;
use App\Models\MajelisPeriodeModel;
use App\Models\ProfilJemaatModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class AdminProfil extends BaseController
{
    protected ProfilJemaatModel $profileModel;
    protected MajelisPeriodeModel $periodeModel;
    protected MajelisAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->profileModel = new ProfilJemaatModel();
        $this->periodeModel = new MajelisPeriodeModel();
        $this->anggotaModel = new MajelisAnggotaModel();
    }

    public function index(): string
    {
        $profile = $this->profileModel->first() ?? [
            'id' => 0,
            'history' => '',
            'vision_mission' => '',
            'address' => '',
            'maps_url' => '',
            'contact_info' => '',
        ];

        $periods = $this->periodeModel->orderBy('start_date', 'DESC')->findAll();
        $selectedPeriod = (int) ($this->request->getGet('period_id') ?: $this->periodeModel->where('is_active', 1)->first()['id'] ?? 0);

        $members = [];
        if ($selectedPeriod > 0) {
            $members = $this->anggotaModel->findByPeriode($selectedPeriod);
        }

        return view('admin/profil/index', [
            'profile' => $profile,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'members' => $members,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function saveProfile(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $history = trim((string) $this->request->getPost('history'));
        $visionMission = trim((string) $this->request->getPost('vision_mission'));
        $address = trim((string) $this->request->getPost('address'));
        $mapsUrl = trim((string) $this->request->getPost('maps_url'));
        $contactInfo = trim((string) $this->request->getPost('contact_info'));

        if ($history === '' || $visionMission === '' || $address === '' || $contactInfo === '') {
            return redirect()->back()->withInput()->with('error', 'Semua field profil Jemaat wajib diisi.');
        }

        $adminId = (int) session()->get('admin_id');
        $data = [
            'history' => $history,
            'vision_mission' => $visionMission,
            'address' => $address,
            'maps_url' => $mapsUrl,
            'contact_info' => $contactInfo,
            'updated_by' => $adminId,
        ];

        if ($id > 0) {
            $this->profileModel->update($id, $data);
        } else {
            $data['created_by'] = $adminId;
            $this->profileModel->insert($data);
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Data profil Jemaat berhasil disimpan.');
    }

    public function savePeriode(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $periodName = trim((string) $this->request->getPost('period_name'));
        $startDate = trim((string) $this->request->getPost('start_date'));
        $endDate = trim((string) $this->request->getPost('end_date'));
        $isActive = (int) $this->request->getPost('is_active');

        if ($periodName === '' || $startDate === '') {
            return redirect()->back()->withInput()->with('error', 'Nama periode dan tanggal mulai wajib diisi.');
        }

        $adminId = (int) session()->get('admin_id');
        $data = [
            'period_name' => $periodName,
            'start_date' => $startDate,
            'end_date' => $endDate !== '' ? $endDate : null,
            'updated_by' => $adminId,
        ];

        if ($isActive === 1) {
            $this->periodeModel->setActivePeriod($id > 0 ? $id : 0, $adminId);
            $data['is_active'] = 1;
        } else {
            $data['is_active'] = 0;
        }

        if ($id > 0) {
            $this->periodeModel->update($id, $data);
        } else {
            $data['created_by'] = $adminId;
            $this->periodeModel->insert($data);
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Periode kepengurusan berhasil disimpan.');
    }

    public function activatePeriode(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $period = $this->periodeModel->find($id);
        if (! $period) {
            return redirect()->to(site_url('admin/profil'))->with('error', 'Periode tidak ditemukan.');
        }

        $this->periodeModel->setActivePeriod($id, (int) session()->get('admin_id'));
        return redirect()->to(site_url('admin/profil'))->with('success', 'Periode aktif berhasil diperbarui.');
    }

    public function saveAnggota(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $periodeId = (int) $this->request->getPost('periode_id');
        $name = trim((string) $this->request->getPost('name'));
        $position = trim((string) $this->request->getPost('position'));

        if ($periodeId <= 0 || $name === '' || $position === '') {
            return redirect()->back()->withInput()->with('error', 'Periode, nama, dan jabatan anggota wajib diisi.');
        }

        $photoPath = null;
        $uploadedFile = $this->request->getFile('photo');
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handlePhotoUpload($uploadedFile, $id > 0 ? $id : null);
            if ($uploadResult['success'] === false) {
                return redirect()->back()->withInput()->with('error', $uploadResult['message']);
            }
            $photoPath = $uploadResult['file'];
        }

        $data = [
            'periode_id' => $periodeId,
            'name' => $name,
            'position' => $position,
            'updated_by' => (int) session()->get('admin_id'),
        ];

        if ($photoPath !== null) {
            $data['photo_path'] = $photoPath;
        }

        if ($id > 0) {
            $current = $this->anggotaModel->find($id);
            if ($current && empty($photoPath) && ! empty($current['photo_path'])) {
                $data['photo_path'] = $current['photo_path'];
            }
            $this->anggotaModel->update($id, $data);
        } else {
            $data['created_by'] = (int) session()->get('admin_id');
            $this->anggotaModel->insert($data);
        }

        return redirect()->to(site_url('admin/profil?period_id=' . $periodeId))->with('success', 'Data anggota Majelis berhasil disimpan.');
    }

    public function deleteAnggota(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $member = $this->anggotaModel->find($id);
        if (! $member) {
            return redirect()->to(site_url('admin/profil'))->with('error', 'Anggota Majelis tidak ditemukan.');
        }

        if (! empty($member['photo_path']) && file_exists(FCPATH . $member['photo_path'])) {
            @unlink(FCPATH . $member['photo_path']);
        }

        $this->anggotaModel->delete($id);
        return redirect()->to(site_url('admin/profil?period_id=' . (int) $member['periode_id']))->with('success', 'Anggota Majelis berhasil dihapus.');
    }

    protected function handlePhotoUpload(UploadedFile $file, ?int $id = null): array
    {
        if (! $file->isValid()) {
            return ['success' => false, 'message' => 'File foto tidak valid.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getExtension());

        if (! in_array($file->getMimeType(), $allowedMime, true) || ! in_array($ext, $allowedExt, true)) {
            return ['success' => false, 'message' => 'Format foto tidak diizinkan. Gunakan JPG, PNG, atau WEBP.'];
        }

        if ((int) ceil($file->getSize() / 1024) > 2048) {
            return ['success' => false, 'message' => 'Ukuran foto maksimal 2MB.'];
        }

        $directory = WRITEPATH . 'uploads/majelis';
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return ['success' => false, 'message' => 'Direktori upload foto tidak dapat dibuat.'];
        }

        $safeName = $file->getRandomName();
        if (! $file->move($directory, $safeName)) {
            return ['success' => false, 'message' => 'Foto gagal dipindahkan ke server.'];
        }

        $storedPath = 'uploads/majelis/' . basename($safeName);
        if ($id) {
            $current = $this->anggotaModel->find($id);
            if (! empty($current['photo_path']) && file_exists(FCPATH . $current['photo_path'])) {
                @unlink(FCPATH . $current['photo_path']);
            }
        }

        return ['success' => true, 'file' => $storedPath];
    }
}
