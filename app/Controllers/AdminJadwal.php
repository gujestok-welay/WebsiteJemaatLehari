<?php

namespace App\Controllers;

use App\Models\JadwalIbadahModel;

class AdminJadwal extends BaseController
{
    protected JadwalIbadahModel $model;

    public function __construct()
    {
        $this->model = new JadwalIbadahModel();
    }

    public function index(): string
    {
        $term = trim((string) $this->request->getGet('q'));
        $cancelled = (string) $this->request->getGet('cancelled');
        $cancelledValue = in_array($cancelled, ['0', '1'], true) ? (int) $cancelled : null;
        $hari = [
            'minggu' => ['label' => 'Minggu', 'index' => 0],
            'senin' => ['label' => 'Senin', 'index' => 1],
            'selasa' => ['label' => 'Selasa', 'index' => 2],
            'rabu' => ['label' => 'Rabu', 'index' => 3],
            'kamis' => ['label' => 'Kamis', 'index' => 4],
            'jumat' => ['label' => 'Jumat', 'index' => 5],
            'sabtu' => ['label' => 'Sabtu', 'index' => 6],
        ];
        $hariDipilih = (string) $this->request->getGet('hari');
        $hariDipilih = isset($hari[$hariDipilih]) ? $hariDipilih : 'semua';
        $zonaWaktu = new \DateTimeZone('Asia/Jayapura');
        $hariIni = new \DateTimeImmutable('today', $zonaWaktu);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $hariDenganIndeks = array_column($hari, 'index');
        $hariDenganIndeks = array_combine($hariDenganIndeks, array_keys($hari));
        $semuaJadwal = $this->model->findByTerm($term);
        $hitung = ['semua' => count($semuaJadwal), 'aktif' => 0, 'dibatalkan' => 0];

        foreach ($semuaJadwal as &$item) {
            $tanggal = new \DateTimeImmutable((string) $item['tanggal'], $zonaWaktu);
            $indeksHari = (int) $tanggal->format('w');
            $item['hari_key'] = $hariDenganIndeks[$indeksHari];
            $item['tanggal_fmt'] = $tanggal->format('j') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y');
            $item['waktu_fmt'] = substr((string) $item['waktu'], 0, 5);
            $hitung[! empty($item['is_cancelled']) ? 'dibatalkan' : 'aktif']++;
        }
        unset($item);

        $jadwalDenganStatus = array_values(array_filter($semuaJadwal, static function (array $item) use ($cancelledValue): bool {
            return $cancelledValue === null || (int) $item['is_cancelled'] === $cancelledValue;
        }));
        $hitungHari = array_fill_keys(array_keys($hari), 0);
        foreach ($jadwalDenganStatus as $item) {
            $hitungHari[$item['hari_key']]++;
        }
        $jadwal = array_values(array_filter($jadwalDenganStatus, static function (array $item) use ($hariDipilih): bool {
            return $hariDipilih === 'semua' || $item['hari_key'] === $hariDipilih;
        }));

        return $this->renderAdminPage('admin/jadwal/index', [
            'items' => $jadwal,
            'term' => $term,
            'cancelled' => $cancelledValue,
            'hari' => $hari,
            'hitung' => $hitung,
            'hitungHari' => $hitungHari,
            'hariDipilih' => $hariDipilih,
            'hariIni' => $hariDenganIndeks[(int) $hariIni->format('w')],
            'jumlahHasil' => count($jadwal),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'scheduleError' => session()->getFlashdata('schedule_error') ?? '',
        ], 'Jadwal Ibadah');
    }

    public function create(): string
    {
        return $this->renderAdminPage('admin/jadwal/form', [
            'item' => null,
            'error' => session()->getFlashdata('schedule_error') ?? session()->getFlashdata('error'),
        ], 'Tambah Jadwal');
    }

    public function edit(int $id): string
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal tidak ditemukan.');
        }

        return $this->renderAdminPage('admin/jadwal/form', [
            'item' => $item,
            'error' => session()->getFlashdata('schedule_error') ?? session()->getFlashdata('error'),
        ], 'Ubah Jadwal');
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $jenis = trim((string) $this->request->getPost('jenis_ibadah'));
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $waktu = trim((string) $this->request->getPost('waktu'));
        $lokasi = trim((string) $this->request->getPost('lokasi'));
        $pelayan = trim((string) $this->request->getPost('pelayan'));
        $cancelled = (int) $this->request->getPost('is_cancelled');

        if ($jenis === '' || $tanggal === '' || $waktu === '' || $lokasi === '') {
            $message = 'Jenis ibadah, tanggal, waktu, dan lokasi wajib diisi.';
            $returnTo = (string) $this->request->getPost('return_to');
            if (in_array($returnTo, ['admin/dashboard', 'admin/jadwal'], true)) {
                return redirect()->to(site_url($returnTo))->withInput()->with('schedule_error', $message);
            }

            return redirect()->back()->withInput()->with('error', $message);
        }

        $data = [
            'jenis_ibadah' => $jenis,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'lokasi' => $lokasi,
            'pelayan' => $pelayan !== '' ? $pelayan : null,
            'is_cancelled' => $cancelled === 1 ? 1 : 0,
            'updated_by' => (int) session()->get('admin_id'),
        ];

        if ($id > 0) {
            $this->model->update($id, $data);
            return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil diperbarui.');
        }

        $data['created_by'] = (int) session()->get('admin_id');
        $this->model->insert($data);

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal baru berhasil disimpan.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal dibatalkan dari daftar aktif.');
    }
}
