<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<div class="news-detail">
    <div class="site-frame news-detail-nav">
        <a href="<?= site_url('berita') ?>">Kembali ke daftar berita</a>
    </div>

    <article aria-labelledby="berita-detail">
        <header class="news-detail-header site-frame">
            <h1 id="berita-detail"><?= esc($item['title']) ?></h1>
            <p class="news-detail-meta">
                <?php if (! empty($dateTime)): ?>
                    <time datetime="<?= esc($dateTime, 'attr') ?>"><?= esc($dateLabel) ?></time>
                <?php else: ?>
                    <?= esc($dateLabel) ?>
                <?php endif; ?>
                <?php if (! empty($isSample)): ?>
                    <span class="sample-content-badge">Contoh</span>
                <?php endif; ?>
            </p>
        </header>

        <div class="news-detail-reading">
            <?php if (! empty($coverImageUrl)): ?>
                <figure class="news-detail-cover">
                    <img src="<?= esc($coverImageUrl, 'attr') ?>" alt="<?= esc($item['title'], 'attr') ?>" width="1200" height="800">
                </figure>
            <?php endif; ?>

            <?php if (! empty($item['summary'])): ?>
                <p class="news-detail-summary"><?= esc($item['summary']) ?></p>
            <?php endif; ?>

            <div class="news-detail-copy"><?= nl2br(esc((string) ($item['content'] ?? ''))) ?></div>
        </div>
    </article>
</div>
<?= $this->endSection() ?>
