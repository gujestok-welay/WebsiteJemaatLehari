<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<div class="page-intro site-frame"><h1>Berita jemaat</h1><p>Kabar kegiatan dan pelayanan dalam satu tempat. Semua artikel di bawah ini merupakan konten dummy.</p></div>
<section class="site-frame news-archive" aria-labelledby="daftar-berita"><h2 id="daftar-berita">Warta & kegiatan</h2>
    <?php foreach ($news as $item): ?>
        <article class="news-row" id="<?= esc($item['id'], 'attr') ?>"><div><h3><?= esc($item['title']) ?></h3><p class="meta">Dummy · <?= esc($item['category']) ?></p><p class="meta"><?= esc($item['date']) ?> · Tanggal contoh</p></div><div><p><?= esc($item['summary']) ?></p><details><summary>Baca isi contoh</summary><p><?= esc($item['body']) ?></p></details></div></article>
    <?php endforeach ?>
</section>
<?= $this->endSection() ?>
