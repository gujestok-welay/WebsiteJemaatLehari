<footer class="site-footer">
    <div class="container-xl footer-inner">
        <div class="row g-4">
            <section class="col-12 col-md-6 col-lg-5" aria-labelledby="footer-brand">
                <h2 class="footer-brand" id="footer-brand">
                    <img
                        class="footer-brand-logo"
                        src="<?= base_url('assets/images/logo/footer-brand.png') ?>"
                        alt="Jemaat Leahari"
                        width="128"
                        height="150">
                </h2>
                <p class="footer-tagline">Informasi yang mendekatkan.</p>

            </section>

            <nav class="col-6 col-md-3 col-lg-3 footer-links" aria-labelledby="footer-links">
                <h2 class="footer-heading" id="footer-links">Tautan cepat</h2>
                <a class="css-link home-inline-link" href="<?= site_url('/') ?>">Beranda</a>
                <a class="css-link home-inline-link" href="<?= site_url('profil') ?>">Profil</a>
                <a class="css-link home-inline-link" href="<?= site_url('berita') ?>">Berita</a>
                <a class="css-link home-inline-link" href="<?= site_url('galeri') ?>">Galeri</a>
                <a class="css-link home-inline-link" href="<?= site_url('jadwal-ibadah') ?>">Jadwal ibadah</a>
            </nav>

            <section class="col-12 col-md-6 col-lg-4" aria-labelledby="footer-social">
                <h2 class="footer-heading" id="footer-social">Ikuti kami</h2>
                <a
                    class="footer-social-link"
                    href="https://www.facebook.com/profile.php?id=61574771007981"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Buka Facebook Jemaat Leahari di tab baru">
                    <svg class="footer-social-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.019 4.388 11.008 10.125 11.878v-8.398H7.078v-3.48h3.047V9.421c0-3.026 1.792-4.698 4.533-4.698 1.312 0 2.686.236 2.686.236v2.976h-1.514c-1.491 0-1.956.931-1.956 1.887v2.25h3.328l-.532 3.48h-2.796v8.398C19.612 23.08 24 18.091 24 12.073Z" fill="currentColor"/>
                    </svg>
                    Facebook <span aria-hidden="true">↗</span>
                </a>
            </section>
        </div>

        <div class="footer-bottom">
            <p>Gereja Protestan Maluku Jemaat Leahari</p>
            <a href="#konten" aria-label="Kembali ke konten">
                <svg class="footer-return-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 19V5m-7 7 7-7 7 7"/>
                </svg>
            </a>
        </div>
    </div>
</footer>