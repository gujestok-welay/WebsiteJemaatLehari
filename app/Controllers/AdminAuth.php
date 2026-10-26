<?php

namespace App\Controllers;

class AdminAuth extends BaseController
{
    public function loginForm(): string
    {
        if (session()->get('admin_id')) {
            return redirect()->to(site_url('admin/dashboard'))->getBody();
        }

        return view('admin/login', ['error' => session()->getFlashdata('error')]);
    }

    public function login()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $ip = (string) $this->request->getIPAddress();
        $identityHash = hash('sha256', $ip . '|' . $email);
        $db = db_connect();
        $now = time();

        $db->table('admin_login_attempts')->where('attempted_at <', $now - 900)->delete();
        $attempts = $db->table('admin_login_attempts')->where('identity_hash', $identityHash)->countAllResults();
        if ($attempts >= 5) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.');
        }

        $admin = $db->table('admin_users')->where('email', $email)->get()->getRowArray();
        if (! $admin || ! password_verify($password, $admin['password_hash'])) {
            $db->table('admin_login_attempts')->insert(['identity_hash' => $identityHash, 'attempted_at' => $now]);
            return redirect()->back()->withInput()->with('error', 'Email atau kata sandi tidak sesuai.');
        }

        $db->table('admin_login_attempts')->where('identity_hash', $identityHash)->delete();
        session()->regenerate(true);
        session()->set(['admin_id' => (int) $admin['id'], 'admin_name' => $admin['name']]);

        return redirect()->to(site_url('admin/dashboard'));
    }

    public function dashboard(): string
    {
        return view('admin/dashboard', ['name' => session()->get('admin_name')]);
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_name']);
        session()->regenerate(true);
        return redirect()->to(site_url('admin/login'));
    }
}
