<?php

namespace App\Controllers;

use App\Models\MajelisAnggotaModel;
use App\Models\MajelisPeriodeModel;
use App\Models\ProfilJemaatModel;
use App\Libraries\PublicProfileContent;
use App\Libraries\ProfileDashboardModules;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;
use JsonException;

class AdminProfil extends BaseController
{
    private const MAX_MODULE_JSON_BYTES = 60 * 1024;
    private const MAX_SERVICE_MAP_BYTES = 5 * 1024 * 1024;
    private const MANAGEMENT_MODULES = [
        'territory' => 'Gambaran Umum Wilayah',
        'census' => 'Jumlah Kepala Keluarga dan Jiwa',
        'worship' => 'Pelayanan Peribadahan',
        'map' => 'Peta Pelayanan',
        'content' => 'Konten Profil Publik',
        'data' => 'Data Jemaat',
        'leadership' => 'Pengelolaan Majelis',
    ];

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
        return $this->renderAdminPage('admin/profil/index', $this->profilePageData() + [
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ], 'Profil & Majelis');
    }

    public function manage(string $module): string
    {
        if (! array_key_exists($module, self::MANAGEMENT_MODULES)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderAdminPage('admin/profil/manage', $this->profilePageData() + [
            'editorModule' => $module,
            'editorTitle' => self::MANAGEMENT_MODULES[$module],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ], 'Kelola ' . self::MANAGEMENT_MODULES[$module]);
    }

    private function profilePageData(): array
    {
        $profile = $this->profileModel->first() ?? [
            'id' => 0,
            'history' => '',
            'vision_mission' => '',
            'address' => '',
            'maps_url' => '',
            'contact_info' => '',
            'page_content' => null,
        ];

        $pageContent = PublicProfileContent::fromProfile($profile);
        $oldContent = old('content');
        if (is_array($oldContent)) {
            $pageContent = PublicProfileContent::mergeForDisplay(PublicProfileContent::defaults(), $oldContent);
        }

        $censusSummary = ProfileDashboardModules::censusSummary($pageContent['census']);
        $pageContent['stats'][0]['value'] = (string) $censusSummary['totalJiwa'];
        $pageContent['stats'][1]['value'] = (string) $censusSummary['householdCount'];
        $territorySaved = trim((string) ($profile['territory_content'] ?? '')) !== '';
        $censusSaved = trim((string) ($profile['census_content'] ?? '')) !== '';
        $serviceMapPath = trim((string) ($profile['service_map_path'] ?? ''));
        if ($serviceMapPath === '') {
            $serviceMapPath = 'assets/images/gereja/Peta Pelayanan.png';
        }
        $serviceMapAlt = trim((string) ($profile['service_map_alt'] ?? ''));
        if ($serviceMapAlt === '') {
            $serviceMapAlt = 'Peta pelayanan Jemaat Leahari';
        }
        $serviceMapTitle = trim((string) ($profile['service_map_title'] ?? ''));
        if ($serviceMapTitle === '') {
            $serviceMapTitle = 'Peta Pelayanan';
        }

        $periods = $this->periodeModel->orderBy('start_date', 'DESC')->findAll();
        $selectedPeriod = (int) ($this->request->getGet('period_id') ?: $this->periodeModel->where('is_active', 1)->first()['id'] ?? 0);

        $members = [];
        if ($selectedPeriod > 0) {
            $members = $this->anggotaModel->findByPeriode($selectedPeriod);
        }

        $editPeriodId = (int) $this->request->getGet('edit_period');
        $editPeriod = $editPeriodId > 0 ? $this->periodeModel->find($editPeriodId) : null;
        $editMemberId = (int) $this->request->getGet('edit_member');
        $editMember = $editMemberId > 0 ? $this->anggotaModel->find($editMemberId) : null;
        if ($editMember !== null && (int) $editMember['periode_id'] !== $selectedPeriod) {
            $editMember = null;
        }

        return [
            'profile' => $profile,
            'pageContent' => $pageContent,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'members' => $members,
            'territorySummary' => count($pageContent['territory']['boundaries']) . ' batas · ' . count($pageContent['seasons']['items']) . ' musim',
            'territoryStatus' => $territorySaved ? 'Tersimpan' : 'Belum diperbarui',
            'censusStatus' => $censusSaved ? 'Tersimpan' : 'Konten awal',
            'householdCount' => $censusSummary['householdCount'],
            'totalJiwa' => $censusSummary['totalJiwa'],
            'largestAgeGroups' => $censusSummary['largestAgeGroups'],
            'serviceMapPath' => $serviceMapPath,
            'serviceMapTitle' => $serviceMapTitle,
            'serviceMapAlt' => $serviceMapAlt,
            'serviceMapSaved' => ! empty($profile['service_map_path']),
            'editPeriod' => $editPeriod,
            'editMember' => $editMember,
        ];
    }

    public function saveTerritoryModule(): ResponseInterface
    {
        try {
            $module = ProfileDashboardModules::normalizeTerritory($this->request->getPost());
            $json = json_encode($module, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (InvalidArgumentException | JsonException $exception) {
            return $this->moduleError($exception->getMessage(), 422);
        }

        if (strlen($json) > self::MAX_MODULE_JSON_BYTES) {
            return $this->moduleError('Data wilayah dan musim maksimal 60 KB.', 422);
        }

        if (! $this->persistProfileColumns(['territory_content' => $json])) {
            return $this->moduleError('Data wilayah gagal disimpan. Coba kembali.', 500);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Gambaran umum wilayah berhasil disimpan.',
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
            'summary' => [
                'territoryStatus' => 'Tersimpan',
                'territorySummary' => count($module['territory']['boundaries']) . ' batas · ' . count($module['seasons']['items']) . ' musim',
            ],
        ]);
    }

    public function saveCensusModule(): ResponseInterface
    {
        try {
            $census = ProfileDashboardModules::normalizeCensus($this->request->getPost());
            $json = json_encode($census, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (InvalidArgumentException | JsonException $exception) {
            return $this->moduleError($exception->getMessage(), 422);
        }

        if (strlen($json) > self::MAX_MODULE_JSON_BYTES) {
            return $this->moduleError('Data jumlah KK dan kelompok usia maksimal 60 KB.', 422);
        }

        if (! $this->persistProfileColumns(['census_content' => $json])) {
            return $this->moduleError('Data jumlah KK dan jiwa gagal disimpan. Coba kembali.', 500);
        }

        $summary = ProfileDashboardModules::censusSummary($census);

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Data jumlah KK dan jiwa berhasil disimpan.',
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
            'summary' => $summary,
        ]);
    }

    public function saveServiceMap(): ResponseInterface
    {
        $post = $this->request->getPost();
        $profile = $this->profileModel->first() ?? [];
        $altInput = $post['service_map_alt'] ?? null;
        if (! is_string($altInput)) {
            return $this->moduleError('Alt text wajib berupa teks.', 422, 'service_map_alt');
        }
        $alt = trim($altInput);
        if ($alt === '' || mb_strlen($alt) > 255) {
            return $this->moduleError('Alt text wajib diisi dan maksimal 255 karakter.', 422, 'service_map_alt');
        }
        $titleInput = $post['service_map_title'] ?? null;
        if ($titleInput !== null && ! is_string($titleInput)) {
            return $this->moduleError('Judul peta harus berupa teks.', 422, 'service_map_title');
        }
        $title = trim((string) ($titleInput ?? $profile['service_map_title'] ?? 'Peta Pelayanan'));
        if ($title === '' || mb_strlen($title) > 150) {
            return $this->moduleError('Judul peta wajib diisi dan maksimal 150 karakter.', 422, 'service_map_title');
        }

        $file = $this->request->getFile('service_map');
        $fileWasUploaded = $file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE;
        if (! $fileWasUploaded) {
            if (! $this->persistProfileColumns([
                'service_map_title' => $title,
                'service_map_alt' => $alt,
            ])) {
                return $this->moduleError('Judul dan alt text peta gagal disimpan. Coba kembali.', 500);
            }

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Judul dan alt text peta berhasil disimpan.',
                'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
                'summary' => ['serviceMapTitle' => $title, 'serviceMapAlt' => $alt],
            ]);
        }

        if (! $file->isValid()) {
            $message = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Ukuran peta maksimal 5 MB.'
                : 'File peta tidak valid. Pilih ulang gambar lalu coba kembali.';
            return $this->moduleError($message, 422, 'service_map');
        }

        if ($file->getSize() > self::MAX_SERVICE_MAP_BYTES) {
            return $this->moduleError('Ukuran peta maksimal 5 MB.', 422, 'service_map');
        }

        $allowedTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $extension = strtolower($file->getExtension());
        $mime = $file->getMimeType();
        $imageInfo = getimagesize($file->getTempName());
        if (! isset($allowedTypes[$extension]) || $allowedTypes[$extension] !== $mime || $imageInfo === false || $imageInfo['mime'] !== $mime) {
            return $this->moduleError('Format gambar tidak valid. Gunakan JPG, PNG, atau WEBP.', 422, 'service_map');
        }

        $directory = FCPATH . 'uploads/profil/peta';
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return $this->moduleError('Direktori penyimpanan peta tidak dapat dibuat.', 500);
        }

        $fileName = $file->getRandomName();
        if (! $file->move($directory, $fileName)) {
            return $this->moduleError('Gambar peta gagal diunggah. Coba kembali.', 500);
        }

        $storedPath = 'uploads/profil/peta/' . basename($fileName);
        $saved = false;
        try {
            $saved = $this->persistProfileColumns([
                'service_map_path' => $storedPath,
                'service_map_title' => $title,
                'service_map_alt' => $alt,
            ]);
        } finally {
            if (! $saved && is_file(FCPATH . $storedPath)) {
                unlink(FCPATH . $storedPath);
            }
        }

        if (! $saved) {
            return $this->moduleError('Gambar peta sudah diunggah, tetapi gagal disimpan. Peta lama tetap digunakan.', 500);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Peta pelayanan berhasil diperbarui.',
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
            'summary' => [
                'serviceMapPath' => $storedPath,
                'serviceMapTitle' => $title,
                'serviceMapUrl' => base_url($storedPath),
                'serviceMapAlt' => $alt,
                'serviceMapStatus' => 'Tersimpan',
            ],
        ]);
    }

    private function persistProfileColumns(array $columns): bool
    {
        $now = date('Y-m-d H:i:s');
        $data = $columns + [
            'updated_by' => (int) session()->get('admin_id'),
            'updated_at' => $now,
        ];
        $profile = $this->profileModel->first();
        $builder = db_connect()->table('profil_jemaat');

        if ($profile !== null) {
            return $builder->where('id', (int) $profile['id'])->update($data);
        }

        return $builder->insert($data + [
            'created_by' => (int) session()->get('admin_id'),
            'created_at' => $now,
        ]);
    }

    private function moduleError(string $message, int $status, ?string $field = null): ResponseInterface
    {
        $response = [
            'success' => false,
            'message' => $message,
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ];
        if ($field !== null) {
            $response['errors'] = [$field => $message];
        }

        return $this->response->setStatusCode($status)->setJSON($response);
    }

    public function saveProfile(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');
        $history = trim((string) $this->request->getPost('history'));
        $visionMission = trim((string) $this->request->getPost('vision_mission'));
        $address = trim((string) $this->request->getPost('address'));
        $mapsUrl = trim((string) $this->request->getPost('maps_url'));
        $contactInfo = trim((string) $this->request->getPost('contact_info'));

        if ($address === '') {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Alamat wajib diisi.', 422, [
                    'address' => 'Alamat wajib diisi.',
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Alamat Jemaat wajib diisi.');
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
            $saved = $this->profileModel->update($id, $data);
        } else {
            $data['created_by'] = $adminId;
            $saved = $this->profileModel->insert($data);
        }

        if ($saved === false) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Data jemaat gagal disimpan. Periksa kembali isian lalu coba lagi.', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Data jemaat gagal disimpan. Periksa kembali isian lalu coba lagi.');
        }

        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Data jemaat berhasil disimpan.');
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Data profil Jemaat berhasil disimpan.');
    }

    public function savePageContent(): ResponseInterface
    {
        $input = $this->request->getPost('content');
        if (! is_array($input)) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Konten halaman profil tidak terkirim. Periksa kembali lalu simpan ulang.', 422);
            }
            return redirect()->back()->with('error', 'Konten halaman profil tidak terkirim. Periksa kembali lalu simpan ulang.');
        }

        $profile = $this->profileModel->first();
        $currentContent = PublicProfileContent::fromProfile($profile ?? []);
        $input = array_replace($currentContent, $input);

        try {
            $content = PublicProfileContent::normalize($input);
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (InvalidArgumentException | JsonException $exception) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError($exception->getMessage(), 422);
            }
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        if (strlen($json) > 60 * 1024) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Konten halaman maksimal 60 KB. Kurangi panjang teks atau jumlah item.', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Konten halaman maksimal 60 KB. Kurangi panjang teks atau jumlah item.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'page_content' => $json,
            'updated_by' => (int) session()->get('admin_id'),
            'updated_at' => $now,
        ];

        $builder = db_connect()->table('profil_jemaat');
        if ($profile !== null) {
            $saved = $builder->where('id', (int) $profile['id'])->update($data);
        } else {
            $data['created_by'] = (int) session()->get('admin_id');
            $data['created_at'] = $now;
            $saved = $builder->insert($data);
        }

        if (! $saved) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Konten halaman profil gagal disimpan. Coba kembali.', 500);
            }
            return redirect()->back()->withInput()->with('error', 'Konten halaman profil gagal disimpan. Coba kembali.');
        }

        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Konten halaman Profil publik berhasil disimpan.');
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Konten halaman profil publik berhasil disimpan.');
    }

    public function savePeriode(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');
        $periodName = trim((string) $this->request->getPost('period_name'));
        $startDate = trim((string) $this->request->getPost('start_date'));
        $endDate = trim((string) $this->request->getPost('end_date'));
        $isActive = (int) $this->request->getPost('is_active');

        if ($periodName === '' || $startDate === '') {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Nama periode dan tanggal mulai wajib diisi.', 422, [
                    'period_name' => 'Nama periode wajib diisi.',
                    'start_date' => 'Tanggal mulai wajib diisi.',
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Nama periode dan tanggal mulai wajib diisi.');
        }

        $adminId = (int) session()->get('admin_id');
        $data = [
            'period_name' => $periodName,
            'start_date' => $startDate,
            'end_date' => $endDate !== '' ? $endDate : null,
            'updated_by' => $adminId,
        ];

        if ($id > 0) {
            if ($isActive === 1) {
                $this->periodeModel->setActivePeriod($id, $adminId);
            }
            $data['is_active'] = $isActive === 1 ? 1 : 0;
            $saved = $this->periodeModel->update($id, $data);
        } else {
            $data['is_active'] = 0;
            $data['created_by'] = $adminId;
            $newId = $this->periodeModel->insert($data);
            $saved = $newId !== false;
            if ($newId !== false && $isActive === 1) {
                $this->periodeModel->setActivePeriod((int) $newId, $adminId);
            }
        }

        if ($saved === false) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Periode Majelis gagal disimpan. Periksa data lalu coba kembali.', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Periode Majelis gagal disimpan. Periksa data lalu coba kembali.');
        }

        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Periode kepengurusan berhasil disimpan.');
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Periode kepengurusan berhasil disimpan.');
    }

    public function activatePeriode(int $id): ResponseInterface
    {
        $period = $this->periodeModel->find($id);
        if (! $period) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Periode tidak ditemukan. Muat ulang halaman lalu coba kembali.', 404);
            }
            return redirect()->to(site_url('admin/profil'))->with('error', 'Periode tidak ditemukan.');
        }

        $this->periodeModel->setActivePeriod($id, (int) session()->get('admin_id'));
        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Periode aktif berhasil diperbarui.');
        }

        return redirect()->to(site_url('admin/profil'))->with('success', 'Periode aktif berhasil diperbarui.');
    }

    public function saveAnggota(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');
        $periodeId = (int) $this->request->getPost('periode_id');
        $name = trim((string) $this->request->getPost('name'));
        $position = trim((string) $this->request->getPost('position'));

        if ($periodeId <= 0 || $name === '' || $position === '') {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Periode, nama, dan jabatan anggota wajib diisi.', 422, [
                    'periode_id' => 'Pilih periode Majelis.',
                    'name' => 'Nama anggota wajib diisi.',
                    'position' => 'Jabatan wajib diisi.',
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Periode, nama, dan jabatan anggota wajib diisi.');
        }

        $photoPath = null;
        $uploadedFile = $this->request->getFile('photo');
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handlePhotoUpload($uploadedFile, $id > 0 ? $id : null);
            if ($uploadResult['success'] === false) {
                if ($this->request->isAJAX()) {
                    return $this->profileAjaxError($uploadResult['message'], 422, ['photo' => $uploadResult['message']]);
                }
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
            $saved = $this->anggotaModel->update($id, $data);
        } else {
            $data['created_by'] = (int) session()->get('admin_id');
            $saved = $this->anggotaModel->insert($data);
        }

        if ($saved === false) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Data anggota Majelis gagal disimpan. Periksa kembali isian.', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Data anggota Majelis gagal disimpan. Periksa kembali isian.');
        }

        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Data anggota Majelis berhasil disimpan.');
        }

        return redirect()->to(site_url('admin/profil?period_id=' . $periodeId))->with('success', 'Data anggota Majelis berhasil disimpan.');
    }

    public function deleteAnggota(int $id): ResponseInterface
    {
        $member = $this->anggotaModel->find($id);
        if (! $member) {
            if ($this->request->isAJAX()) {
                return $this->profileAjaxError('Anggota Majelis tidak ditemukan. Muat ulang halaman lalu coba kembali.', 404);
            }
            return redirect()->to(site_url('admin/profil'))->with('error', 'Anggota Majelis tidak ditemukan.');
        }

        if (! empty($member['photo_path']) && file_exists(FCPATH . $member['photo_path'])) {
            @unlink(FCPATH . $member['photo_path']);
        }

        $this->anggotaModel->delete($id);
        if ($this->request->isAJAX()) {
            return $this->profileAjaxSuccess('Anggota Majelis berhasil dihapus.');
        }

        return redirect()->to(site_url('admin/profil?period_id=' . (int) $member['periode_id']))->with('success', 'Anggota Majelis berhasil dihapus.');
    }

    private function profileAjaxSuccess(string $message): ResponseInterface
    {
        session()->setFlashdata('success', $message);

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => $message,
            'csrf' => [
                'name' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ]);
    }

    private function profileAjaxError(string $message, int $status, array $errors = []): ResponseInterface
    {
        $response = [
            'success' => false,
            'message' => $message,
            'csrf' => [
                'name' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];
        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return $this->response->setStatusCode($status)->setJSON($response);
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

        $directory = FCPATH . 'uploads/majelis';
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
