<?php

namespace App\Controllers;

use App\Models\JadwalIbadahModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AdminJadwal extends BaseController
{
    protected JadwalIbadahModel $model;

    public function __construct()
    {
        $this->model = new JadwalIbadahModel();
    }

    public function index(): string
    {
        $termValue = $this->request->getGet('q');
        $term = is_string($termValue) ? trim($termValue) : '';
        $cancelledValue = $this->request->getGet('cancelled');
        $cancelled = is_string($cancelledValue) ? $cancelledValue : '';
        $cancelledValue = in_array($cancelled, ['0', '1'], true) ? (int) $cancelled : null;
        $archived = $this->request->getGet('archived') === '1';
        $hari = [
            'minggu' => ['label' => 'Minggu', 'index' => 0],
            'senin' => ['label' => 'Senin', 'index' => 1],
            'selasa' => ['label' => 'Selasa', 'index' => 2],
            'rabu' => ['label' => 'Rabu', 'index' => 3],
            'kamis' => ['label' => 'Kamis', 'index' => 4],
            'jumat' => ['label' => 'Jumat', 'index' => 5],
            'sabtu' => ['label' => 'Sabtu', 'index' => 6],
        ];
        $hariValue = $this->request->getGet('hari');
        $hariDipilih = is_string($hariValue) ? $hariValue : '';
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
        $jadwalArsip = $this->model->findByTerm($term, null, true);
        $hitung = [
            'semua' => count($semuaJadwal),
            'aktif' => 0,
            'dibatalkan' => 0,
            'arsip' => count($jadwalArsip),
        ];

        foreach ($semuaJadwal as $item) {
            $hitung[! empty($item['is_cancelled']) ? 'dibatalkan' : 'aktif']++;
        }

        $jadwalSumber = $archived ? $jadwalArsip : $semuaJadwal;
        foreach ($jadwalSumber as &$item) {
            $tanggal = new \DateTimeImmutable((string) $item['tanggal'], $zonaWaktu);
            $indeksHari = (int) $tanggal->format('w');
            $item['hari_key'] = $hariDenganIndeks[$indeksHari];
            $item['tanggal_fmt'] = $tanggal->format('j') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y');
            $item['waktu_fmt'] = substr((string) $item['waktu'], 0, 5);
        }
        unset($item);

        $jadwalDenganStatus = array_values(array_filter($jadwalSumber, static function (array $item) use ($cancelledValue, $archived): bool {
            return $archived || $cancelledValue === null || (int) $item['is_cancelled'] === $cancelledValue;
        }));
        $hitungHari = array_fill_keys(array_keys($hari), 0);
        foreach ($jadwalDenganStatus as $item) {
            $hitungHari[$item['hari_key']]++;
        }
        $jadwal = array_values(array_filter($jadwalDenganStatus, static function (array $item) use ($hariDipilih): bool {
            return $hariDipilih === 'semua' || $item['hari_key'] === $hariDipilih;
        }));

        $viewData = [
            'items' => $jadwal,
            'term' => $term,
            'cancelled' => $cancelledValue,
            'archived' => $archived,
            'hari' => $hari,
            'hitung' => $hitung,
            'hitungHari' => $hitungHari,
            'hariDipilih' => $hariDipilih,
            'hariIni' => $hariDenganIndeks[(int) $hariIni->format('w')],
            'jumlahHasil' => count($jadwal),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'scheduleError' => session()->getFlashdata('schedule_error') ?? '',
            'scheduleModalOpen' => $this->request->getGet('create') === '1',
        ];

        if ($this->request->getHeaderLine('X-Schedule-Partial') === '1') {
            $this->response
                ->setHeader('Vary', 'X-Schedule-Partial')
                ->setHeader('X-Schedule-Partial-Response', '1')
                ->setHeader('Cache-Control', 'no-store');

            return view('admin/jadwal/_filters-results', $viewData);
        }

        return $this->renderAdminPage('admin/jadwal/index', $viewData, 'Jadwal Ibadah');
    }

    public function create(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(site_url('admin/jadwal?create=1'));
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        try {
            $item = $this->model->find($id);
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal memuat jadwal ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/jadwal'))
                ->with('error', 'Jadwal gagal dimuat. Silakan coba lagi.');
        }

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
        $returnToValue = $this->request->getPost('return_to');
        $returnTo = is_string($returnToValue) && in_array($returnToValue, ['admin/dashboard', 'admin/jadwal'], true)
            ? $returnToValue
            : 'admin/jadwal';
        $idValue = $this->request->getPost('id');
        $id = 0;
        if ($idValue !== null) {
            if ((! is_string($idValue) && ! is_int($idValue))
                || ! ctype_digit((string) $idValue)
                || (int) $idValue < 1) {
                return redirect()->to(site_url('admin/jadwal'))
                    ->with('error', 'ID jadwal tidak valid.');
            }

            $id = (int) $idValue;
        }

        $fields = [];
        foreach (['jenis_ibadah', 'tanggal', 'waktu', 'lokasi', 'pelayan'] as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null && ! is_string($value)) {
                return $this->redirectWithSaveError('Data jadwal tidak valid. Periksa kembali isian formulir.', $id, $returnTo);
            }

            $value = $value ?? '';
            if (! mb_check_encoding($value, 'UTF-8')) {
                return $this->redirectWithSaveError('Data jadwal tidak valid. Periksa kembali isian formulir.', $id, $returnTo);
            }

            $normalized = preg_replace('/\A[\p{Z}\s]+|[\p{Z}\s]+\z/u', '', $value);
            if ($normalized === null) {
                return $this->redirectWithSaveError('Data jadwal tidak valid. Periksa kembali isian formulir.', $id, $returnTo);
            }
            $fields[$field] = $normalized;
        }

        $cancelledValue = $this->request->getPost('is_cancelled');
        if ((! is_string($cancelledValue) && ! is_int($cancelledValue))
            || ! in_array((string) $cancelledValue, ['0', '1'], true)) {
            return $this->redirectWithSaveError('Status jadwal tidak valid.', $id, $returnTo);
        }
        $cancelled = (int) $cancelledValue;

        if ($fields['jenis_ibadah'] === '' || $fields['tanggal'] === '' || $fields['waktu'] === '' || $fields['lokasi'] === '') {
            return $this->redirectWithSaveError('Jenis ibadah, tanggal, waktu, dan lokasi wajib diisi.', $id, $returnTo);
        }

        foreach (['jenis_ibadah', 'lokasi', 'pelayan'] as $field) {
            if (preg_match('/[\x00-\x1F\x7F]/', $fields[$field]) === 1) {
                $labels = [
                    'jenis_ibadah' => 'Jenis ibadah',
                    'lokasi' => 'Lokasi',
                    'pelayan' => 'Pelayan',
                ];

                return $this->redirectWithSaveError(
                    $labels[$field] . ' mengandung karakter kontrol yang tidak valid.',
                    $id,
                    $returnTo,
                );
            }
        }

        if (! preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $fields['tanggal'], $dateParts)
            || ! checkdate((int) $dateParts[2], (int) $dateParts[3], (int) $dateParts[1])) {
            return $this->redirectWithSaveError(
                'Tanggal tidak valid. Gunakan format YYYY-MM-DD.',
                $id,
                $returnTo,
            );
        }

        if (! preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?\z/', $fields['waktu'])) {
            return $this->redirectWithSaveError(
                'Waktu tidak valid. Gunakan format HH:MM.',
                $id,
                $returnTo,
            );
        }

        $data = [
            'jenis_ibadah' => $fields['jenis_ibadah'],
            'tanggal' => $fields['tanggal'],
            'waktu' => $fields['waktu'],
            'lokasi' => $fields['lokasi'],
            'pelayan' => $fields['pelayan'] !== '' ? $fields['pelayan'] : null,
            'is_cancelled' => $cancelled,
            'updated_by' => (int) session()->get('admin_id'),
        ];

        try {
            if ($id > 0) {
                if (! $this->model->find($id)) {
                    return redirect()->to(site_url('admin/jadwal'))
                        ->with('error', 'Jadwal tidak ditemukan.');
                }

                $saved = $this->model->update($id, $data);
                if ($saved === false) {
                    return $this->redirectWithSaveError(
                        $this->validationErrorMessage('Jadwal gagal diperbarui. Periksa kembali data dan coba lagi.'),
                        $id,
                        $returnTo,
                    );
                }

                return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil diperbarui.');
            }

            $data['created_by'] = (int) session()->get('admin_id');
            $saved = $this->model->insert($data);
            if ($saved === false) {
                return $this->redirectWithSaveError(
                    $this->validationErrorMessage('Jadwal gagal disimpan. Periksa kembali data dan coba lagi.'),
                    0,
                    $returnTo,
                );
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal menyimpan jadwal: {message}', ['message' => $exception->getMessage()]);

            return $this->redirectWithSaveError('Jadwal gagal disimpan. Silakan coba lagi.', $id, $returnTo);
        }

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal baru berhasil disimpan.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        return $this->archive($id);
    }

    public function cancel(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        return $this->updateCancellationStatus(
            $id,
            1,
            'Jadwal berhasil dibatalkan.',
            'Jadwal sudah dibatalkan.',
        );
    }

    public function reactivate(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        return $this->updateCancellationStatus(
            $id,
            0,
            'Jadwal berhasil diaktifkan kembali.',
            'Jadwal sudah aktif.',
        );
    }

    protected function updateCancellationStatus(
        int $id,
        int $status,
        string $successMessage,
        string $unchangedMessage,
    ): \CodeIgniter\HTTP\RedirectResponse {
        try {
            $item = $this->model->find($id);
            if (! $item) {
                return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal tidak ditemukan.');
            }

            if ((int) $item['is_cancelled'] === $status) {
                return redirect()->to(site_url('admin/jadwal'))->with('error', $unchangedMessage);
            }

            if ($this->model->update($id, [
                'is_cancelled' => $status,
                'updated_by' => (int) session()->get('admin_id'),
            ]) === false) {
                return redirect()->to(site_url('admin/jadwal'))
                    ->with('error', 'Jadwal gagal diperbarui. Silakan coba lagi.');
            }

            $updated = $this->model->find($id);
            if (! $updated || (int) $updated['is_cancelled'] !== $status) {
                return redirect()->to(site_url('admin/jadwal'))
                    ->with('error', 'Jadwal gagal diperbarui. Silakan coba lagi.');
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal mengubah status jadwal ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/jadwal'))
                ->with('error', 'Jadwal gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/jadwal'))->with('success', $successMessage);
    }

    public function archive(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        try {
            $item = $this->model->find($id);
            if (! $item) {
                return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal tidak ditemukan.');
            }

            $deleted = $this->model->delete($id);
            if ($deleted !== false && ! $this->model->findArchivedById($id)) {
                $deleted = false;
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal mengarsipkan jadwal ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/jadwal'))
                ->with('error', 'Jadwal gagal diarsipkan. Silakan coba lagi.');
        }

        if ($deleted === false) {
            return redirect()->to(site_url('admin/jadwal'))
                ->with('error', 'Jadwal gagal diarsipkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil diarsipkan.');
    }

    public function restore(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        try {
            if (! $this->model->findArchivedById($id)) {
                return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal arsip tidak ditemukan.');
            }

            $restored = $this->model->restoreArchived($id);
            if ($restored === false || ! $this->model->find($id)) {
                return redirect()->to(site_url('admin/jadwal'))
                    ->with('error', 'Jadwal gagal dipulihkan. Silakan coba lagi.');
            }
        } catch (DatabaseException $exception) {
            log_message('error', 'Gagal memulihkan jadwal ID {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('admin/jadwal'))
                ->with('error', 'Jadwal gagal dipulihkan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('admin/jadwal'))->with('success', 'Jadwal berhasil dipulihkan.');
    }

    protected function redirectWithSaveError(string $message, int $id, string $returnTo): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($id > 0) {
            return redirect()
                ->to(site_url('admin/jadwal/edit/' . $id))
                ->withInput()
                ->with('error', $message);
        }

        $url = site_url($returnTo);
        if ($returnTo === 'admin/jadwal') {
            $url .= '?create=1';
        }

        return redirect()
            ->to($url)
            ->withInput()
            ->with('schedule_error', $message);
    }

    protected function validationErrorMessage(string $fallback): string
    {
        $errors = array_filter($this->model->errors());
        $labels = [
            'jenis_ibadah' => 'Jenis ibadah',
            'tanggal' => 'Tanggal',
            'waktu' => 'Waktu',
            'lokasi' => 'Lokasi',
            'pelayan' => 'Pelayan',
            'is_cancelled' => 'Status jadwal',
        ];
        foreach ($errors as $field => &$error) {
            $error = str_replace((string) $field, $labels[$field] ?? (string) $field, $error);
        }
        unset($error);

        return $errors === [] ? $fallback : implode(' ', $errors);
    }
}
