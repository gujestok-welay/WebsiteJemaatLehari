<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | Jemaat Leahari</title>
    <meta name="description" content="Pratinjau website informasi Jemaat Leahari: profil, berita, dan jadwal ibadah. Seluruh konten masih berupa contoh.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preload" href="<?= base_url('assets/fonts/cormorant-garamond/latin-600-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url('assets/fonts/dm-sans/latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/publik.css') ?>">
    <script defer src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script defer src="<?= base_url('assets/js/publik.js') ?>"></script>
</head>
<body<?= $activePage === 'beranda' ? ' class="page-home"' : '' ?>>
<a class="skip-link" href="#konten">Lewati ke konten</a>
<?= $this->include('partials/navbar') ?>
<aside class="demo-notice"><div class="container-fluid site-frame"><strong>Pratinjau dengan konten dummy.</strong> Berita, profil, dan jadwal belum menjadi informasi resmi jemaat.</div></aside>
<main id="konten" tabindex="-1">
    <?= $this->renderSection('content') ?>
</main>
<?= $this->include('partials/footer') ?>
</body>
</html>
