<header class="site-header">
    <nav class="navbar navbar-expand-md container-fluid site-frame" aria-label="Navigasi utama">
        <a class="navbar-brand" href="<?= site_url('/') ?>" aria-label="Beranda utama Jemaat Leahari">
            <img class="navbar-brand-logo" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" alt="Logo GPM" width="52" height="52">
            <span class="navbar-brand-text">
                <span class="navbar-brand-name">Jemaat Leahari</span>
                <span class="navbar-brand-sub">Maluku</span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigasi" aria-controls="navigasi" aria-expanded="false" aria-label="Buka atau tutup navigasi">Menu</button>
        <div class="collapse navbar-collapse" id="navigasi">
            <ul class="navbar-nav ms-auto">
                <?php foreach (['beranda' => ['/', 'Beranda'], 'profil' => ['profil', 'Profil'], 'berita' => ['berita', 'Berita'], 'jadwal' => ['jadwal-ibadah', 'Jadwal Ibadah']] as $key => [$path, $label]): ?>
                    <li class="nav-item"><a class="nav-link<?= $activePage === $key ? ' active' : '' ?>" <?= $activePage === $key ? 'aria-current="page"' : '' ?> href="<?= site_url($path) ?>"><?= esc($label) ?></a></li>
                <?php endforeach ?>
            </ul>
        </div>
    </nav>
</header>
<noscript><style>.navbar-toggler{display:none}.navbar-collapse.collapse{display:block}</style></noscript>
