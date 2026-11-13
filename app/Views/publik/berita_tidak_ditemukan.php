<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<section class="news-not-found site-frame" aria-labelledby="news-not-found-title">
    <h1 id="news-not-found-title">Berita tidak ditemukan</h1>
    <p>Tautan ini mungkin sudah tidak berlaku atau berita tidak tersedia untuk publik.</p>
    <a class="btn btn-primary" href="<?= site_url('berita') ?>">Lihat semua berita</a>
</section>
<?= $this->endSection() ?>
