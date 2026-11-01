<?php

namespace App\Controllers;

use App\Models\StatistikAgregatModel;

class AdminStatistik extends BaseController
{
    protected StatistikAgregatModel $model;

    public function __construct()
    {
        $this->model = new StatistikAgregatModel();
    }

    public function index(): string
    {
        $items = $this->model->orderBy('updated_at', 'DESC')->findAll();

        return view('admin/statistik/index', [
            'items' => $items,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $this->request->getPost('id');
        $label = trim((string) $this->request->getPost('metric_label'));
        $value = trim((string) $this->request->getPost('metric_value'));
        $unit = trim((string) $this->request->getPost('unit_label'));

        if ($label === '' || $value === '' || ! is_numeric($value)) {
            return redirect()->back()->withInput()->with('error', 'Label dan nilai statistik wajib diisi serta berbentuk angka.');
        }

        $adminId = (int) session()->get('admin_id');
        $data = [
            'metric_label' => $label,
            'metric_value' => (float) $value,
            'unit_label' => $unit !== '' ? $unit : null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $adminId,
        ];

        if ($id > 0) {
            $this->model->update($id, $data);
        } else {
            $data['created_by'] = $adminId;
            $this->model->insert($data);
        }

        return redirect()->to(site_url('admin/statistik'))->with('success', 'Data statistik agregat berhasil disimpan.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/statistik'))->with('error', 'Data statistik tidak ditemukan.');
        }

        $this->model->delete($id);
        return redirect()->to(site_url('admin/statistik'))->with('success', 'Data statistik berhasil dihapus.');
    }
}
