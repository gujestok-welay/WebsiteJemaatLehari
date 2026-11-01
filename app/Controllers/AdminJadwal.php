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
        $cancelled = $this->request->getGet('cancelled');
        $cancelledValue = $cancelled === '' ? null : (int) $cancelled;
        $items = $this->model->findByTerm($term, $cancelledValue);

        return view('admin/jadwal/index', [
            'items' => $items,
            'term' => $term,
            'cancelled' => $cancelledValue,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create(): string
    {
        return view('admin/jadwal/form', [
            'item' => null,
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function edit(int $id): string
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/jadwal'))->with('error', 'Jadwal tidak ditemukan.');
        }

        return view('admin/jadwal/form', [
            'item' => $item,
            'error' => session()->getFlashdata('error'),
        ]);
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
            return redirect()->back()->withInput()->with('error', 'Jenis ibadah, tanggal, waktu, dan lokasi wajib diisi.');
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
