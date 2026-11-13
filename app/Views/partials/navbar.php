<header class="site-header">
    <nav class="navbar navbar-expand-lg container-fluid site-frame" aria-label="Navigasi utama">
        <a class="navbar-brand" href="<?= site_url('/') ?>" aria-label="Beranda utama Jemaat Leahari">
            <img class="navbar-brand-logo" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" alt="Logo GPM" width="52" height="52">
            <img class="navbar-brand-wordmark" src="<?= base_url('assets/images/logo/footer-brand.png') ?>" alt="" width="50" height="58">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navigasi" aria-controls="navigasi" aria-expanded="false" aria-label="Buka navigasi utama">
            <svg class="navbar-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div class="offcanvas-lg offcanvas-start navbar-collapse public-nav-drawer" tabindex="-1" id="navigasi" aria-labelledby="navigasiLabel">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title" id="navigasiLabel">Navigasi utama</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup navigasi"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto">
                    <?php foreach (['beranda' => ['/', 'Beranda'], 'profil' => ['profil', 'Profil'], 'berita' => ['berita', 'Berita'], 'jadwal' => ['jadwal-ibadah', 'Jadwal Ibadah']] as $key => [$path, $label]): ?>
                        <li class="nav-item"><a class="nav-link<?= $activePage === $key ? ' active' : '' ?>" <?= $activePage === $key ? 'aria-current="page"' : '' ?> href="<?= site_url($path) ?>"><?= esc($label) ?></a></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<noscript>
    <style>
        @media (max-width: 991.98px) {

            .navbar-toggler,
            .public-nav-drawer .offcanvas-header {
                display: none !important;
            }

            .public-nav-drawer.offcanvas-lg {
                position: static !important;
                z-index: auto !important;
                display: block !important;
                width: auto !important;
                height: auto !important;
                visibility: visible !important;
                background: transparent !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                transform: none !important;
                transition: none !important;
            }

            .public-nav-drawer .offcanvas-body {
                display: block !important;
                padding: 0 !important;
                overflow: visible !important;
            }
        }
    </style>
</noscript>