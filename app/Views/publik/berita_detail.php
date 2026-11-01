<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<div class="page-intro site-frame">
    <h1><?= esc($item['title']) ?></h1>
    <p><?= esc(date('d F Y', strtotime($item['created_at'] ?? 'now'))) ?></p>
</div>

<article class="site-frame news-archive" aria-labelledby="berita-detail">
    <h2 id="berita-detail" class="visually-hidden"><?= esc($item['title']) ?></h2>
    <?php if (! empty($item['cover_image'])): ?>
        <figure class="home-hero-photo">
            <img src="<?= esc($item['cover_image'], 'attr') ?>" alt="<?= esc($item['title']) ?>" width="1200" height="800">
        </figure>
    <?php endif; ?>

    <div class="news-row">
        <div>
            <?php if (! empty($item['summary'])): ?>
                <p class="intro-text"><?= esc($item['summary']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <?= $item['content'] ?>
        </div>
    </div>

    <p><a href="<?= site_url('berita') ?>">← Kembali ke daftar berita</a></p>
</article>
<?= $this->endSection() ?>
