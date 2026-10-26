<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<section class="home-hero site-frame" aria-labelledby="judul-beranda">
    <div class="home-hero-copy">
        <h1 id="judul-beranda">Jemaat <br>Leahari</h1>
        <p class="home-hero-intro">Ruang informasi jemaat di Maluku untuk mengikuti berita, mengenal jemaat, dan menemukan jadwal ibadah.</p>
        <div class="home-hero-actions">
            <a class="btn btn-gold" href="<?= site_url('jadwal-ibadah') ?>">Lihat jadwal ibadah <span aria-hidden="true">↗</span></a>
            <a href="#warta">Berita jemaat</a>
        </div>
    </div>
    <figure class="home-hero-photo">
        <img src="<?= base_url('assets/images/gereja/Gereja-tampak-depan.jpeg') ?>" alt="Tampak depan gedung gereja Jemaat Leahari" width="1600" height="1200" fetchpriority="high">
        <figcaption>Gedung gereja Jemaat Leahari</figcaption>
    </figure>
</section>

<div class="home-updates site-frame">
<section class="home-news" aria-labelledby="warta">
    <div class="section-heading"><h2 id="warta">Berita jemaat</h2><a href="<?= site_url('berita') ?>">Semua berita <span aria-hidden="true">↗</span></a></div>
    <div class="news-list">
        <?php if (empty($news)): ?>
            <p class="home-empty">Belum ada berita yang tersedia.</p>
        <?php else: ?>
        <?php foreach (array_slice($news, 0, 2) as $item): ?>
            <article class="news-row"><div><h3><a href="<?= site_url('berita') . '#' . esc($item['id'], 'attr') ?>"><?= esc($item['title']) ?></a></h3><p class="meta">Dummy · <?= esc($item['category']) ?> · <?= esc($item['date']) ?></p></div><div><p><?= esc($item['summary']) ?></p><p class="meta">Tanggal contoh</p></div></article>
        <?php endforeach ?>
        <?php endif ?>
    </div>
</section>

<section class="home-schedule" aria-labelledby="jadwal-contoh">
    <div class="schedule-intro"><h2 id="jadwal-contoh">Jadwal ibadah</h2><p>Contoh tampilan jadwal. Pastikan waktu dan tempat melalui informasi resmi jemaat.</p><a href="<?= site_url('jadwal-ibadah') ?>">Lihat seluruh jadwal contoh <span aria-hidden="true">↗</span></a></div>
    <?php $scheduleHighlight = empty($schedules) ? null : reset($schedules); ?>
    <?php if ($scheduleHighlight !== null): ?>
        <div class="schedule-highlight"><p class="meta">Sorotan jadwal contoh</p><h3><?= esc($scheduleHighlight['name']) ?></h3><p class="meta">Dummy · <?= esc($scheduleHighlight['day']) ?></p><p class="service-time"><?= esc($scheduleHighlight['time']) ?> <span>WIT</span></p><p><?= esc($scheduleHighlight['location']) ?></p></div>
    <?php else: ?>
        <p class="home-empty">Belum ada jadwal ibadah yang tersedia.</p>
    <?php endif ?>
</section>
</div>

<section class="section-space site-frame about-preview" aria-labelledby="kenali"><h2 id="kenali">Mengenal jemaat</h2><div><p class="intro-text">Profil menjadi tempat mengenal sejarah, pelayanan, dan orang-orang yang melayani di Jemaat Leahari.</p><p>Isi profil masih menunggu data resmi yang disetujui untuk publikasi.</p><a href="<?= site_url('profil') ?>">Baca profil jemaat <span aria-hidden="true">↗</span></a></div></section>
<?= $this->endSection() ?>
