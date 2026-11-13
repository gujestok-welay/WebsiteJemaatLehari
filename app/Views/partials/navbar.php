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
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#navigasi" aria-label="Tutup navigasi"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto">
                    <?php foreach (['beranda' => ['/', 'Beranda'], 'profil' => ['profil', 'Profil'], 'berita' => ['berita', 'Berita'], 'galeri' => ['galeri', 'Galeri'], 'jadwal' => ['jadwal-ibadah', 'Jadwal Ibadah']] as $key => [$path, $label]): ?>
                        <?php if ($key === 'profil'): ?>
                            <li class="nav-item dropdown">
                                <button
                                    class="nav-link dropdown-toggle profile-dropdown-toggle<?= $activePage === $key ? ' active' : '' ?>"
                                    type="button"
                                    id="profil-menu-toggle"
                                    data-bs-toggle="dropdown"
                                    <?= $activePage === $key ? 'aria-current="page"' : '' ?>
                                    aria-expanded="false"
                                    aria-controls="profil-menu">
                                    <?= esc($label) ?>
                                    <svg class="profile-dropdown-chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="m4 6 4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <ul class="dropdown-menu profile-dropdown-menu" id="profil-menu" aria-labelledby="profil-menu-toggle">
                                    <?php foreach ([
                                        'Peta Pelayanan' => 'peta-pelayanan',
                                        'Majelis Jemaat' => 'majelis-jemaat',
                                        'Gambaran Umum Wilayah Pelayanan' => 'wilayah',
                                        'Jumlah Kepala Keluarga dan Jiwa' => 'jemaat',
                                        'Pelayanan Peribadahan' => 'ibadah',
                                    ] as $sectionLabel => $sectionId): ?>
                                        <li><a class="dropdown-item" href="<?= site_url('profil') ?>#<?= esc($sectionId, 'attr') ?>"><?= esc($sectionLabel) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link<?= $activePage === $key ? ' active' : '' ?>" <?= $activePage === $key ? 'aria-current="page"' : '' ?> href="<?= site_url($path) ?>"><?= esc($label) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach ?>
                    <li class="nav-item theme-control">
                        <?= view('components/theme-toggle') ?>
                    </li>
                    <li class="nav-item admin-entry-item">
                        <a class="nav-link admin-entry-link" href="<?= site_url('admin') ?>" aria-label="Masuk ke panel pengelola" title="Masuk ke panel pengelola">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<noscript>
    <style>
        .profile-dropdown-toggle {
            display: none !important;
        }

        .profile-dropdown-menu {
            position: absolute !important;
            inset: 100% auto auto 0 !important;
            display: block !important;
        }

        .profile-dropdown-menu .dropdown-divider {
            display: none !important;
        }

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

            .public-nav-drawer .profile-dropdown-menu {
                position: static !important;
                inset: auto !important;
                width: 100% !important;
                min-width: 0 !important;
            }
        }
    </style>
</noscript>