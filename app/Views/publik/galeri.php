<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<div class="gallery-page">
    <header class="gallery-page-heading">
        <div class="gallery-page-heading-inner">
            <h1>Galeri kegiatan</h1>
            <p>Poster dan dokumentasi kegiatan Jemaat Leahari.</p>
        </div>
    </header>

    <section class="gallery-collection" aria-label="Dokumentasi kegiatan">
        <div class="gallery-collection-inner">
            <?php if ($activities === []): ?>
                <div class="gallery-empty">
                    <h2>Belum ada dokumentasi kegiatan</h2>
                    <p>Dokumentasi yang telah diterbitkan akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($activities as $activity): ?>
                        <article class="gallery-card">
                            <a
                                class="gallery-card-link"
                                href="<?= site_url('galeri/' . rawurlencode((string) $activity['slug'])) ?>"
                                aria-label="<?= esc('Lihat dokumentasi: ' . (string) $activity['title'], 'attr') ?>"
                            >
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
                                            class="gallery-card-image"
                                            src="<?= esc($activity['poster_url'], 'attr') ?>"
                                            alt="<?= esc($activity['title'] . ' — ' . ucfirst((string) $activity['poster_type']), 'attr') ?>"
                                            loading="lazy"
                                        >
                                        <span class="gallery-card-image-fallback" hidden>Poster kegiatan tidak dapat dimuat</span>
                                    <?php else: ?>
                                        <span class="gallery-card-image-fallback">Poster kegiatan tidak tersedia</span>
                                    <?php endif; ?>
                                </span>
                                <span class="gallery-card-copy">
                                    <?php if (! empty($activity['date_label'])): ?>
                                        <time datetime="<?= esc($activity['event_date'], 'attr') ?>"><?= esc($activity['date_label']) ?></time>
                                    <?php endif; ?>
                                    <strong><?= esc($activity['title']) ?></strong>
                                    <?php if (! empty($activity['description'])): ?>
                                        <span class="gallery-card-description"><?= esc($activity['description']) ?></span>
                                    <?php endif; ?>
                                    <span class="gallery-card-read">Lihat dokumentasi</span>
                                </span>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
