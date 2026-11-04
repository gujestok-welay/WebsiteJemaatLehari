<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<?php
$quickLinks = [
    ['profil', 'Profil Jemaat', 'Sejarah, pelayanan, dan identitas jemaat.'],
    ['berita', 'Berita Jemaat', 'Kabar terbaru dan kegiatan bersama.'],
    ['jadwal-ibadah', 'Jadwal Ibadah', 'Waktu, lokasi, dan agenda ibadah.'],
];
$nextService = $schedules[0] ?? null;
$latestNews = array_slice($news, 0, 2);
?>

<section class="home-shell">
    <div class="container-xl py-4 py-lg-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <p class="home-kicker">Jemaat GPM Leahari</p>
                <h1 class="home-title" id="judul-beranda">Jemaat <span>Leahari</span></h1>
                <p class="home-intro">Ruang informasi jemaat untuk mengikuti berita, mengenal pelayanan, dan
                    merencanakan kehadiran dalam ibadah.</p>

                <div class="d-flex flex-wrap align-items-center gap-3 home-actions">
                    <a class="btn btn-gold" href="<?= site_url('jadwal-ibadah') ?>">
                        <span class="btn-gold-label">Lihat jadwal ibadah</span>
                        <span class="btn-gold-icon" aria-hidden="true">↗</span>
                    </a>
                    <?= view('components/css-link', [
                        'url' => site_url('berita'),
                        'label' => 'Berita jemaat',
                        'className' => 'home-inline-link',
                        'icon' => '',
                    ]) ?>
                </div>


            </div>

            <div class="col-lg-5">
                <aside class="hero-panel" aria-label="Ibadah terdekat"
                    style="background-image: linear-gradient(180deg, rgba(15, 10, 18, 0.12), rgba(15, 10, 18, 0.28)), url('<?= base_url('assets/images/gereja/Gereja-tampak-depan.jpeg') ?>');">


                    <?php if ($nextService): ?>
                        <div class="hero-panel-card">
                            <p class="panel-kicker"><span class="kicker-dot"></span> Ibadah terdekat</p>
                            <h2><?= esc($nextService['name']) ?></h2>
                            <p class="panel-date"><?= esc($nextService['day_label']) ?>, <?= esc($nextService['date']) ?>
                            </p>

                            <hr class="panel-divider">

                            <div class="panel-meta">
                                <span class="panel-time">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 6v6l4 2"></path>
                                    </svg>
                                    <?= esc($nextService['time']) ?> WIT
                                </span>
                                <?= view('components/css-link', [
                                    'url' => site_url('jadwal-ibadah'),
                                    'label' => 'Detail',
                                    'icon' => '→',
                                    'className' => 'panel-link',
                                ]) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="hero-panel-card">
                            <p class="panel-kicker"><span class="kicker-dot"
                                    style="background-color: var(--muted, #6B6275);"></span> Ibadah terdekat</p>
                            <h2>Belum ada jadwal</h2>
                            <p class="panel-date">Jadwal akan segera ditambahkan.</p>
                        </div>
                    <?php endif ?>
                </aside>
            </div>
        </div>
    </div>

    <div class="container-xl pb-4 pb-lg-5">
        <section class="leadership-section" aria-labelledby="kepemimpinan">
            <div class="section-heading leadership-heading">
                <h2 id="kepemimpinan">Majelis Jemaat</h2>
            </div>

            <div class="leadership-grid">
                <?php
                $leaders = [
                    ['name' => 'Pdt. Ny. J. K. Tutupary/L', 'role' => 'Ketua Majelis Jemaat', 'image' => 'assets/images/gereja/Ketua-Majelis-Jemaat.jpeg'],
                    ['name' => 'Pdt. J. N. W. Tualeka', 'role' => 'Wakil Ketua Majelis Jemaat', 'image' => 'assets/images/gereja/Wakil-Ketua-Majelis-Jemaat.jpeg'],
                    ['name' => 'A. L. T. R. Siahaya', 'role' => 'Bendahara Jemaat', 'image' => 'assets/images/gereja/Bendahara-Jemaat.jpeg'],

                ];
                ?>


                <?php foreach ($leaders as $leader): ?>
                    <article class="leader-card"
                        style="--leader-image: url('<?= base_url(esc($leader['image'], 'attr')) ?>');">
                        <div class="leader-card-body">
                            <h3><?= esc($leader['name']) ?></h3>
                            <p><?= esc($leader['role']) ?></p>
                        </div>
                    </article>
                <?php endforeach ?>
            </div>
        </section>
    </div>

    <div class="container-xl home-updates">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <section class="home-news" aria-labelledby="warta">
                    <div class="section-heading">
                        <h2 id="warta">Berita jemaat</h2>
                        <?= view('components/css-link', [
                            'url' => site_url('berita'),
                            'label' => 'Semua berita',
                            'icon' => '↗',
                            'className' => '',
                        ]) ?>
                    </div>

                    <?php if (empty($news)): ?>
                        <p class="home-empty">Belum ada berita yang tersedia.</p>
                    <?php else: ?>
                        <div class="news-list">
                            <?php foreach ($latestNews as $item): ?>
                                <article class="news-card card rounded-4 shadow-sm overflow-hidden">
                                    <div class="news-card-thumb" aria-hidden="true">Warta</div>
                                    <div class="news-card-copy card-body">
                                        <p class="meta small text-body-secondary"><?= ! empty($isSample) ? 'Contoh · ' : '' ?><?= esc($item['category'] ?? 'Berita') ?> ·
                                            <?= esc($item['date'] ?? '') ?>
                                        </p>
                                        <h3><?= view('components/css-link', [
                                            'url' => $item['url'],
                                            'label' => $item['title'],
                                            'className' => '',
                                            'icon' => '',
                                        ]) ?></h3>
                                        <p><?= esc($item['summary']) ?></p>
                                        <?= view('components/css-link', [
                                            'url' => $item['url'],
                                            'label' => 'Baca selengkapnya',
                                            'icon' => '→',
                                            'className' => 'inline-link fw-semibold text-decoration-none',
                                        ]) ?>
                                    </div>
                                </article>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="home-schedule card shadow-sm" aria-labelledby="jadwal-contoh">
                    <div class="schedule-intro">
                        <h2 id="jadwal-contoh">Jadwal ibadah</h2>
                        <p>Contoh tampilan jadwal. Pastikan waktu dan tempat melalui informasi resmi jemaat.</p>
                        <?= view('components/css-link', [
                            'url' => site_url('jadwal-ibadah'),
                            'label' => 'Lihat seluruh jadwal',
                            'icon' => '↗',
                            'className' => '',
                        ]) ?>
                    </div>

                    <div class="schedule-list">
                        <?php foreach (array_slice($schedules, 0, 4) as $schedule): ?>
                            <div class="schedule-item">
                                <div class="schedule-day">
                                    <strong><?= date('d', strtotime($schedule['date'])) ?></strong>
                                    <span><?= date('M', strtotime($schedule['date'])) ?></span>
                                </div>
                                <div class="schedule-copy">
                                    <h3><?= esc($schedule['name']) ?></h3>
                                    <p><?= esc($schedule['day_label']) ?></p>
                                    <p><?= esc($schedule['time']) ?> WIT · <?= esc($schedule['location']) ?></p>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <div class="container-xl pb-4 pb-lg-5">
        <div class="row g-4 home-connection">
            <div class="col-md-5">
                <div class="verse-panel">
                    <p class="verse-label">Kebersamaan</p>
                    <blockquote>“Bersama dalam iman, pelayanan, dan sukacita, Jemaat Leahari terus tumbuh dalam
                        persekutuan.”</blockquote>
                </div>
            </div>
            <div class="col-md-7">
                <div class="connect-panel">
                    <p class="panel-kicker">Media Center</p>
                    <h2>Jemaat tumbuh bukan hanya dalam ibadah, tetapi dalam pelayanan bersama.</h2>
                    <p>Informasi resmi tetap mengikuti saluran yang telah ditetapkan, agar setiap pembaruan dapat dibaca
                        dengan jelas dan konsisten.</p>
                    <?= view('components/css-link', [
                        'url' => site_url('profil'),
                        'label' => 'Baca profil jemaat',
                        'icon' => '↗',
                        'className' => '',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <section class="about-preview container-xl" aria-labelledby="kenali">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-4">
                <h2 id="kenali">Mengenal jemaat</h2>
            </div>
            <div class="col-lg-8">
                <div>
                    <p class="intro-text">Profil menjadi tempat mengenal sejarah, pelayanan, dan orang-orang yang
                        melayani di Jemaat Leahari.</p>
                    <p>Isi profil masih menunggu data resmi yang disetujui untuk publikasi.</p>
                    <?= view('components/css-link', [
                        'url' => site_url('profil'),
                        'label' => 'Baca profil jemaat',
                        'icon' => '↗',
                        'className' => '',
                    ]) ?>
                </div>
            </div>
        </div>
    </section>
</section>
<?= $this->endSection() ?>