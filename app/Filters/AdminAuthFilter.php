<?php

namespace App\Filters;

use App\Models\AdminUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $adminId = session()->get('admin_id');
        if ($adminId === null) {
            return redirect()->to(site_url('admin/login'));
        }

        if ((! is_int($adminId) && ! is_string($adminId))
            || ! ctype_digit((string) $adminId)
            || (int) $adminId < 1) {
            return $this->denyAdminAccess();
        }

        try {
            $admin = db_connect()->table('admin_users')
                ->select('id')
                ->where('id', (int) $adminId)
                ->where('is_active', 1)
                ->where('deleted_at', null)
                ->whereIn('role', AdminUserModel::getRoleOptions())
                ->get()
                ->getRowArray();
        } catch (\Throwable $exception) {
            log_message('error', 'Gagal memverifikasi akun admin ID {id}: {message}', [
                'id' => (int) $adminId,
                'message' => $exception->getMessage(),
            ]);

            return $this->denyAdminAccess();
        }

        if ($admin === null) {
            return $this->denyAdminAccess();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function denyAdminAccess(): RedirectResponse
    {
        session()->remove(['admin_id', 'admin_name']);
        session()->regenerate(true);
        session()->setFlashdata('error', 'Akun admin tidak aktif atau tidak dapat diverifikasi. Silakan masuk kembali.');

        return redirect()->to(site_url('admin/login'));
    }
}
