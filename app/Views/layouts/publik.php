<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | Jemaat Leahari</title>
    <meta name="description"
        content="<?= esc($metaDescription ?? 'Pratinjau website informasi Jemaat Leahari. Sebagian informasi masih menunggu konfirmasi resmi.', 'attr') ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preload" href="<?= base_url('assets/fonts/cormorant-garamond/latin-600-normal.woff2') ?>" as="font"
        type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url('assets/fonts/dm-sans/latin-400-normal.woff2') ?>" as="font"
        type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/publik.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/css-link.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/theme.css') ?>">
    <script src="<?= base_url('assets/js/theme.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/theme.js') ?>"></script>
    <script defer src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script defer src="<?= base_url('assets/js/publik.js') ?>"></script>
</head>
<body<?= $activePage === 'beranda' ? ' class="page-home"' : '' ?>>
    <a class="skip-link" href="#konten">Lewati ke konten</a>
    <?= $this->include('partials/navbar') ?>

    <main id="konten" tabindex="-1">
        <?= $this->renderSection('content') ?>
    </main>
    <?= $this->include('partials/footer') ?>
    </body>

</html>