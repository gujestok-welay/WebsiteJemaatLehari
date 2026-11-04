<?php

namespace App\Controllers;

use App\Models\BeritaModel;

class AdminAuth extends BaseController
{
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        $target = session()->get('admin_id') ? 'admin/dashboard' : 'admin/login';

        return redirect()->to(site_url($target));
    }

    public function loginForm(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('admin_id')) {
            return redirect()->to(site_url('admin/dashboard'));
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
            return redirect()->to(site_url('admin/login'))->withInput()->with('error', 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.');
        }

        $admin = $db->table('admin_users')->where('email', $email)->get()->getRowArray();
        if (! $admin || ! password_verify($password, $admin['password_hash'])) {
            $db->table('admin_login_attempts')->insert(['identity_hash' => $identityHash, 'attempted_at' => $now]);
            return redirect()->to(site_url('admin/login'))->withInput()->with('error', 'Email atau kata sandi tidak sesuai.');
        }

        $db->table('admin_login_attempts')->where('identity_hash', $identityHash)->delete();
        session()->regenerate(true);
        session()->set(['admin_id' => (int) $admin['id'], 'admin_name' => $admin['name']]);

        return redirect()->to(site_url('admin/dashboard'));
    }

    public function dashboard(): string
    {
        $db = db_connect();
        $timezone = new \DateTimeZone('Asia/Jayapura');
        $today = new \DateTimeImmutable('today', $timezone);
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $formatDate = static function (string $date) use ($timezone, $months): string {
            $value = new \DateTimeImmutable($date, $timezone);

            return $value->format('j') . ' ' . $months[(int) $value->format('n')] . ' ' . $value->format('Y');
        };

        $publishedNewsCount = $db->table('berita')
            ->where('deleted_at', null)
            ->where('status', 'Terbit')
            ->countAllResults();
        $draftNewsCount = $db->table('berita')
            ->where('deleted_at', null)
            ->where('status', 'Draft')
            ->countAllResults();
        $upcomingScheduleCount = $db->table('jadwal_ibadah')
            ->where('deleted_at', null)
            ->where('is_cancelled', 0)
            ->where('tanggal >=', $today->format('Y-m-d'))
            ->countAllResults();
        $latestNews = $db->table('berita')
            ->select('id, title, slug, summary, content, status, cover_image, created_at')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $nextSchedule = $db->table('jadwal_ibadah')
            ->select('jenis_ibadah, tanggal, waktu, lokasi')
            ->where('deleted_at', null)
            ->where('is_cancelled', 0)
            ->where('tanggal >=', $today->format('Y-m-d'))
            ->orderBy('tanggal', 'ASC')
            ->orderBy('waktu', 'ASC')
            ->get(1)
            ->getRowArray();

        foreach ($latestNews as &$item) {
            $item['date_label'] = empty($item['created_at']) ? null : $formatDate($item['created_at']);
        }
        unset($item);

        if ($nextSchedule !== null) {
            $nextSchedule['date_label'] = $formatDate($nextSchedule['tanggal']);
            $nextSchedule['time_label'] = substr((string) $nextSchedule['waktu'], 0, 5);
        }

        $newsError = session()->getFlashdata('news_error') ?? '';

        return $this->renderAdminPage('admin/dashboard', [
            'name' => session()->get('admin_name'),
            'todayLabel' => $days[(int) $today->format('w')] . ', ' . $formatDate($today->format('Y-m-d')),
            'publishedNewsCount' => $publishedNewsCount,
            'draftNewsCount' => $draftNewsCount,
            'upcomingScheduleCount' => $upcomingScheduleCount,
            'latestNews' => $latestNews,
            'nextSchedule' => $nextSchedule,
            'scheduleError' => session()->getFlashdata('schedule_error') ?? '',
            'newsError' => $newsError,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'newsModalOpen' => $this->request->getGet('create') === '1'
                || $newsError !== '',
            'statusOptions' => BeritaModel::statusOptions(),
        ], 'Ringkasan');
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_name']);
        session()->regenerate(true);
        return redirect()->to(site_url('admin/login'));
    }
}
