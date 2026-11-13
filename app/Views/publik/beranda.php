<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<?php
$nextService = $nextService ?? null;
$latestNews = array_slice($news, 0, 2);
$featuredActivities = $featuredActivities ?? [];
?>

<section class="home-shell">
    <div class="container-xl py-4 py-lg-5">
        <div class="row align-items-center g-4 g-lg-4">
            <div class="col-lg-7">
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
                            <h2>Ibadah terdekat</h2>
                            <p class="panel-service-name"><?= esc($nextService['name']) ?></p>
                            <p class="panel-date"><?= esc($nextService['day_label']) ?>
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
                            <h2>Jadwal ibadah</h2>
                            <p class="panel-service-name">Belum ada jadwal mendatang</p>
                            <p class="panel-date">Belum ada jadwal ibadah mendatang yang dapat ditampilkan.</p>
                        </div>
                    <?php endif ?>
                </aside>
            </div>
        </div>
    </div>

    <div class="container-xl home-updates">
        <div class="row g-4 g-lg-4">
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
                            <?php foreach ($latestNews as $index => $item): ?>
                                <?= view('components/berita-card', [
                                    'item' => $item,
                                    'index' => $index,
                                    'isSample' => ! empty($isSample),
                                ]) ?>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="home-schedule card shadow-sm" aria-labelledby="jadwal-contoh">
                    <div class="schedule-intro">
                        <h2 id="jadwal-contoh">Jadwal ibadah</h2>
                        <p>Jadwal ibadah aktif yang tersedia.</p>
                        <?= view('components/css-link', [
                            'url' => site_url('jadwal-ibadah'),
                            'label' => 'Lihat seluruh jadwal',
                            'icon' => '↗',
                            'className' => '',
                        ]) ?>
                    </div>

                    <div class="schedule-list">
                        <?php if ($schedules === []): ?>
                            <p>Belum ada jadwal ibadah mendatang yang tersedia saat ini.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($schedules, 0, 4) as $schedule): ?>
                                <div class="schedule-item">
                                    <div class="schedule-day">
                                        <strong><?= esc($schedule['dom']) ?></strong>
                                        <span><?= esc($schedule['dow']) ?></span>
                                    </div>
                                    <div class="schedule-copy">
                                        <h3><?= esc($schedule['name']) ?></h3>
                                        <p><?= esc($schedule['date_label']) ?></p>
                                        <p><?= esc($schedule['time']) ?> WIT · <?= esc($schedule['location']) ?></p>
                                        <?php if (! empty($schedule['minister'])): ?>
                                            <p>Pelayan: <?= esc($schedule['minister']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        <?php endif ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <div class="container-xl pb-4 pb-lg-5">
        <section class="leadership-section" aria-labelledby="kepemimpinan">
            <div class="section-heading leadership-heading">
                <h2 id="kepemimpinan"><?= esc($leadershipTitle) ?></h2>
            </div>

            <?php if (! empty($majelis)): ?>
                <div class="leadership-carousel" data-leadership-carousel role="region" aria-roledescription="carousel" aria-label="Susunan Majelis Jemaat">
                    <div class="leadership-grid" data-carousel-track>
                        <?php foreach ($majelis as $index => $member): ?>
                            <article class="leader-card" data-carousel-slide aria-roledescription="slide" aria-label="<?= esc(($index + 1) . ' dari ' . count($majelis) . ': ' . $member['name'], 'attr') ?>">
                                <?php if (! empty($member['photo_path'])): ?>
                                    <img class="leader-card-image" src="<?= esc(base_url($member['photo_path']), 'attr') ?>" alt="" aria-hidden="true">
                                <?php endif; ?>
                                <div class="leader-card-body">
                                    <h3><?= esc($member['name']) ?></h3>
                                    <p><?= esc($member['position']) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="leadership-carousel-controls" data-carousel-controls hidden>
                        <button class="leadership-carousel-arrow" type="button" data-carousel-previous aria-label="Anggota sebelumnya">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6M21 12H9" /></svg>
                        </button>
                        <div class="leadership-carousel-middle">
                            <div class="leadership-carousel-indicators" role="group" aria-label="Pilih anggota">
                                <?php foreach ($majelis as $index => $member): ?>
                                    <button class="leadership-carousel-indicator" type="button" data-carousel-indicator="<?= $index ?>" aria-label="<?= esc('Tampilkan anggota ' . ($index + 1) . ': ' . $member['name'], 'attr') ?>" aria-pressed="false">
                                        <span aria-hidden="true"></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button class="leadership-carousel-arrow" type="button" data-carousel-next aria-label="Anggota berikutnya">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6m6 6H3" /></svg>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <p class="home-empty"><?= esc($leadershipEmpty) ?></p>
            <?php endif; ?>
        </section>
    </div>

    <section class="home-gallery" aria-labelledby="home-gallery-title">
        <div class="home-gallery-inner container-xl">
            <header class="home-gallery-heading">
                <div>
                    <h2 id="home-gallery-title">Galeri kegiatan</h2>
                    <p>Poster dan dokumentasi kegiatan Jemaat Leahari.</p>
                </div>
                <a class="home-gallery-link" href="<?= site_url('galeri') ?>">
                    Jelajahi galeri
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                </a>
            </header>

            <?php if ($featuredActivities === []): ?>
                <p class="home-gallery-empty">Belum ada dokumentasi kegiatan yang dipilih untuk beranda.</p>
            <?php else: ?>
                <div class="home-gallery-grid">
                    <?php foreach ($featuredActivities as $activity): ?>
                        <article class="home-gallery-card">
                            <a class="home-gallery-card-link" href="<?= site_url('galeri/' . rawurlencode((string) $activity['slug'])) ?>">
                                <span class="gallery-card-image-wrap">
                                    <?php if (! empty($activity['poster_url'])): ?>
                                        <img
                                            class="gallery-card-image-backdrop"
                                            src="<?= esc($activity['poster_url'], 'attr') ?>"
                                            alt=""
                                            aria-hidden="true"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <img
                                            class="home-gallery-image gallery-card-image"
                                            src="<?= esc($activity['poster_url'], 'attr') ?>"
                                            alt="<?= esc($activity['title'] . ' — ' . ucfirst((string) $activity['poster_type']), 'attr') ?>"
                                            width="1200"
                                            height="800"
                                            loading="lazy"
                                        >
                                        <span class="gallery-card-image-fallback" hidden>Poster kegiatan tidak dapat dimuat</span>
                                    <?php else: ?>
                                        <span class="home-gallery-image-fallback gallery-card-image-fallback">Poster kegiatan tidak tersedia</span>
                                    <?php endif; ?>
                                </span>
                                <span class="home-gallery-card-copy">
                                    <?php if (! empty($activity['date_label'])): ?>
                                        <time datetime="<?= esc($activity['event_date'], 'attr') ?>"><?= esc($activity['date_label']) ?></time>
                                    <?php endif; ?>
                                    <strong><?= esc($activity['title']) ?></strong>
                                </span>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</section>
<?= $this->endSection() ?>