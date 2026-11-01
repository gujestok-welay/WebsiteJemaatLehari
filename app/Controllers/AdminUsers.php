<?php

namespace App\Controllers;

use App\Models\AdminUserModel;

class AdminUsers extends BaseController
{
    protected AdminUserModel $model;

    public function __construct()
    {
        $this->model = new AdminUserModel();
    }

    public static function isAllowedAccess(?string $role): bool
    {
        return $role === 'super_admin';
    }

    public function index(): string
    {
        if (! self::isAllowedAccess(session()->get('admin_role'))) {
            return redirect()->to(site_url('admin/dashboard'))->with('error', 'Akses dibatasi hanya untuk Super Admin.');
        }

        $users = $this->model
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/users', [
            'users' => $users,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function store()
    {
        if (! self::isAllowedAccess(session()->get('admin_role'))) {
            return redirect()->to(site_url('admin/dashboard'))->with('error', 'Akses dibatasi hanya untuk Super Admin.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $role = (string) $this->request->getPost('role');

        if ($name === '' || $email === '' || $password === '' || ! in_array($role, AdminUserModel::getRoleOptions(), true)) {
            return redirect()->back()->withInput()->with('error', 'Nama, email, kata sandi, dan peran harus diisi dengan benar.');
        }

        if ($this->model->where('email', $email)->where('deleted_at', null)->first()) {
            return redirect()->back()->withInput()->with('error', 'Email akun sudah digunakan.');
        }

        $result = $this->model->save([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
            'is_active' => 1,
            'created_by' => (int) session()->get('admin_id'),
            'updated_by' => (int) session()->get('admin_id'),
        ]);

        if (! $result) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan akun pengguna.');
        }

        return redirect()->to(site_url('admin/users'))->with('success', 'Akun baru berhasil dibuat.');
    }
}
