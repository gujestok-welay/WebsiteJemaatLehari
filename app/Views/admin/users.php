<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Pengguna · Media Center</title>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        :root {
            --ink:#1d2a25;--muted:#586b62;--ground:#edf3ef;--panel:#fff;--line:#dfe8e1;--accent:#214f41;--accent-strong:#183e34;--soft:#e9f3ee;--focus:#1a73e8;
            --font: "DM Sans", "Segoe UI", Arial, sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--font); background:var(--ground); color:var(--ink); }
        a, button, input, select { outline-offset: 5px; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 3px solid var(--focus); outline-offset: 5px; }
        .topbar { background:var(--panel); border-bottom:1px solid var(--line); }
        .brand { display:flex; align-items:center; gap:12px; font-weight:700; color:var(--ink); text-decoration:none; }
        .brand-mark { display:block; width:38px; height:38px; flex:0 0 auto; object-fit:contain; }
        .main-shell { max-width:1200px; margin:0 auto; padding:32px 20px 60px; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
        .page-head h1 { margin:0; font-size:clamp(2rem, 4vw, 2.8rem); font-weight:700; }
        .muted { color:var(--muted); }
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:12px; box-shadow:0 4px 12px rgba(17, 29, 23, 0.03); }
        .grid { display:grid; grid-template-columns: 1.4fr 0.8fr; gap:20px; }
        .card { padding:24px; }
        .table { margin:0; border-collapse:collapse; width:100%; }
        .table th, .table td { padding:14px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        .table thead th { background:#f6faf7; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--muted); }
        .badge { display:inline-block; padding:6px 10px; border-radius:999px; font-size:0.75rem; font-weight:700; }
        .badge-super { background:#eaf3ee; color:var(--accent-strong); }
        .badge-operator { background:#eef1fa; color:#2f4b87; }
        .btn-shell { display:flex; gap:10px; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; justify-content:center; border-radius:8px; padding:10px 16px; font-weight:600; text-decoration:none; border:1px solid var(--line); cursor:pointer; }
        .btn-primary { background:var(--accent); color:#fff; border-color:var(--accent); }
        .btn-secondary { background:#fff; color:var(--ink); }
        .alert { padding:14px 16px; border-radius:10px; margin-bottom:18px; border:1px solid transparent; }
        .alert-success { background:#eaf7ee; border-color:#bfe0c8; color:#1c5d3d; }
        .alert-error { background:#fff1f0; border-color:#f4c7c3; color:#923126; }
        .form-group { margin-bottom:16px; }
        label { display:block; margin-bottom:8px; font-weight:600; }
        .form-control { width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; background:#fff; color:var(--ink); }
        .select { width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px; background:#fff; }
        @media (max-width: 880px) { .grid { grid-template-columns: 1fr; } .page-head { display:block; } }
    </style>
    <link href="<?= base_url('assets/css/theme.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('assets/js/theme.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/theme.js') ?>"></script>
</head>
<body class="admin-users">
<header class="topbar">
    <div class="container-fluid px-4 py-3 d-flex justify-content-between align-items-center">
        <a class="brand" href="<?= site_url('admin/dashboard') ?>">
            <img class="brand-mark" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" alt="" width="38" height="38">
            <span>Media Center<br>Jemaat Leahari</span>
        </a>
        <div class="theme-control">
            <?= view('components/theme-toggle') ?>
        </div>
        <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn btn-secondary" type="submit">Keluar</button></form>
    </div>
</header>
<main class="main-shell">
    <div class="page-head">
        <div>
            <p class="muted mb-2">Super Admin</p>
            <h1>Manajemen Pengguna</h1>
        </div>
        <div class="btn-shell">
            <a class="btn btn-secondary" href="<?= site_url('admin/dashboard') ?>">Kembali ke dashboard</a>
        </div>
    </div>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="grid">
        <section class="panel card">
            <h2 class="mb-3">Daftar akun</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="muted">Belum ada pengguna terdaftar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= esc($user['name'] ?? '') ?></td>
                                <td><?= esc($user['email'] ?? '') ?></td>
                                <td>
                                    <?php if (($user['role'] ?? '') === 'super_admin'): ?>
                                        <span class="badge badge-super">Super Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-operator">Operator</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= ! empty($user['is_active']) ? 'Aktif' : 'Nonaktif' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel card">
            <h2 class="mb-3">Tambah akun baru</h2>
            <form method="post" action="<?= site_url('admin/users/store') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="name">Nama lengkap</label>
                    <input class="form-control" id="name" name="name" type="text" value="<?= esc(old('name') ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="<?= esc(old('email') ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Peran</label>
                    <select class="select" id="role" name="role" required>
                        <option value="">Pilih role</option>
                        <option value="super_admin" <?= old('role') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="operator" <?= old('role') === 'operator' ? 'selected' : '' ?>>Operator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Kata sandi</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>
                <button class="btn btn-primary" type="submit">Simpan akun</button>
            </form>
        </aside>
    </div>
</main>
</body>
</html>
