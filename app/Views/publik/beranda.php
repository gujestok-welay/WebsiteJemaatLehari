<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<section class="home-hero site-frame" aria-labelledby="judul-beranda">
    <h1 id="judul-beranda">Jemaat Leihari</h1>
    <div class="hero-bottom"><p>Ruang informasi jemaat di Maluku untuk mengikuti berita, mengenal jemaat, dan menemukan jadwal ibadah.</p><a class="btn btn-gold" href="<?= site_url('jadwal-ibadah') ?>">Lihat jadwal ibadah</a></div>
</section>
<section class="section-space site-frame" aria-labelledby="warta">
    <div class="section-heading"><h2 id="warta">Berita jemaat</h2><a href="<?= site_url('berita') ?>">Semua berita</a></div>
    <div class="news-list">
        <?php foreach (array_slice($news, 0, 2) as $item): ?>
            <article class="news-row"><div><h3><a href="<?= site_url('berita') . '#' . esc($item['id'], 'attr') ?>"><?= esc($item['title']) ?></a></h3><p class="meta">Dummy · <?= esc($item['category']) ?></p></div><div><p><?= esc($item['summary']) ?></p><p class="meta"><?= esc($item['date']) ?> · Tanggal contoh</p></div></article>
        <?php endforeach ?>
    </div>
</section>
<section class="home-schedule site-frame" aria-labelledby="jadwal-contoh">
    <div><h2 id="jadwal-contoh">Jadwal ibadah</h2><p>Contoh tampilan jadwal. Pastikan waktu dan tempat melalui informasi resmi jemaat.</p><a href="<?= site_url('jadwal-ibadah') ?>">Lihat seluruh jadwal contoh</a></div>
    <div class="schedule-highlight"><h3><?= esc($schedules[0]['name']) ?></h3><p class="meta">Dummy · <?= esc($schedules[0]['day']) ?></p><p class="service-time"><?= esc($schedules[0]['time']) ?> <span>WIT</span></p><p><?= esc($schedules[0]['location']) ?></p></div>
</section>
<section class="section-space site-frame about-preview" aria-labelledby="kenali"><h2 id="kenali">Mengenal jemaat</h2><div><p class="intro-text">Profil menjadi tempat mengenal sejarah, pelayanan, dan orang-orang yang melayani di Jemaat Leihari.</p><p>Isi profil masih menunggu data resmi yang disetujui untuk publikasi.</p><a href="<?= site_url('profil') ?>">Baca profil jemaat</a></div></section>
<?= $this->endSection() ?>
