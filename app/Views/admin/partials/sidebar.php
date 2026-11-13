<?php $currentPath = trim(uri_string(), '/'); ?>
<aside class="admin-sidebar" id="adminSidebar" aria-label="Navigasi administrasi">
    <a class="sidebar-brand" href="<?= site_url('admin/dashboard') ?>">
        <img class="brand-mark" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" alt="" width="42" height="42">
        <span><strong>Media Center</strong><small>Jemaat Leahari</small></span>
    </a>

    <p class="sidebar-label">Ruang kerja</p>
    <nav class="sidebar-nav">
        <a class="sidebar-link <?= $currentPath === 'admin/dashboard' ? 'is-active' : '' ?>"
           href="<?= site_url('admin/dashboard') ?>" <?= $currentPath === 'admin/dashboard' ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg>
            <span>Ringkasan</span>
        </a>
        <a class="sidebar-link <?= str_starts_with($currentPath, 'admin/berita') ? 'is-active' : '' ?>"
           href="<?= site_url('admin/berita') ?>" <?= str_starts_with($currentPath, 'admin/berita') ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12a2 2 0 0 1 2 2v14H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M7 8h8M7 12h8M7 16h5"/></svg>
            <span>Berita</span>
        </a>
        <a class="sidebar-link <?= str_starts_with($currentPath, 'admin/galeri') ? 'is-active' : '' ?>"
           href="<?= site_url('admin/galeri') ?>" <?= str_starts_with($currentPath, 'admin/galeri') ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg>
            <span>Galeri kegiatan</span>
        </a>
        <a class="sidebar-link <?= str_starts_with($currentPath, 'admin/jadwal') ? 'is-active' : '' ?>"
           href="<?= site_url('admin/jadwal') ?>" <?= str_starts_with($currentPath, 'admin/jadwal') ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h3M8 17h7"/></svg>
            <span>Jadwal ibadah</span>
        </a>
        <a class="sidebar-link <?= str_starts_with($currentPath, 'admin/profil') ? 'is-active' : '' ?>"
           href="<?= site_url('admin/profil') ?>" <?= str_starts_with($currentPath, 'admin/profil') ? 'aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V5a2 2 0 0 1 2-2h8l6 6v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M14 3v6h6M8 13h8M8 17h8"/></svg>
            <span>Profil &amp; Majelis</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-note">Kelola informasi dan publikasi Jemaat dari satu tempat.</div>
        <div class="sidebar-theme-control">
            <span class="sidebar-theme-label">Tema tampilan</span>
            <?= view('components/theme-toggle') ?>
        </div>
    </div>
</aside>
