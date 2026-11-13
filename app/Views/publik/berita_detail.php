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
                <?php if (! empty($isExternal)): ?>
                    <span>Berita dari <?= esc($item['source_name'] ?? 'Sumber eksternal') ?></span>
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

            <?php if (! empty($isExternal)): ?>
                <?php if (! empty($sourceUrl)): ?>
                    <p class="news-detail-source">
                        <a class="btn news-detail-source-link" href="<?= esc($sourceUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                            Baca berita asli <span class="visually-hidden">(membuka tab baru)</span>
                        </a>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="news-detail-copy"><?= nl2br(esc((string) ($item['content'] ?? ''))) ?></div>
            <?php endif; ?>
        </div>
    </article>
</div>
<?= $this->endSection() ?>
