<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title ?? 'Dashboard') ?> · Media Center Jemaat Leahari</title>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<a class="skip-link" href="#main-content">Langsung ke konten utama</a>

<?= $this->include('admin/partials/sidebar') ?>
<button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Tutup menu" hidden></button>

<div class="admin-shell">
    <header class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-controls="adminSidebar" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Menu</span>
        </button>
        <div class="topbar-title"><?= esc($title ?? 'Dashboard') ?></div>
        <div class="topbar-actions">
            <a class="public-link" href="<?= base_url() ?>" target="_blank" rel="noopener">
                Lihat situs
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9"/><path d="M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/></svg>
            </a>
            <span class="account-name"><?= esc($name) ?></span>
            <form method="post" action="<?= site_url('admin/logout') ?>">
                <?= csrf_field() ?>
                <button class="logout-button" type="submit">Keluar</button>
            </form>
        </div>
    </header>

    <main class="admin-content" id="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="admin-footer">Media Center · Jemaat Leahari</footer>
</div>

<script src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
</body>
</html>
