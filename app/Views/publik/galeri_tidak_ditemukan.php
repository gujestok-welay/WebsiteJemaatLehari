<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<section class="gallery-not-found" aria-labelledby="gallery-not-found-title">
    <h1 id="gallery-not-found-title">Kegiatan tidak ditemukan</h1>
    <p>Kegiatan ini tidak tersedia atau belum diterbitkan.</p>
    <a href="<?= site_url('galeri') ?>">Lihat galeri kegiatan</a>
</section>
<?= $this->endSection() ?>
