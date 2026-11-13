<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<?php
$mediaCount = count($activity['media']);
$viewerPhotos = array_map(
    static fn(array $media): array => ['url' => $media['url'], 'label' => $media['label']],
    $activity['media']
);
?>
<article class="gallery-detail" aria-labelledby="gallery-detail-title">
    <div class="gallery-detail-nav">
        <a href="<?= site_url('galeri') ?>">Kembali ke galeri</a>
    </div>

    <header class="gallery-detail-heading">
        <div class="gallery-detail-heading-inner">
            <h1 id="gallery-detail-title"><?= esc($activity['title']) ?></h1>
            <?php if (! empty($activity['date_label'])): ?>
                <time datetime="<?= esc($activity['event_date'], 'attr') ?>"><?= esc($activity['date_label']) ?></time>
            <?php endif; ?>
        </div>
    </header>

    <div class="gallery-detail-content">
        <?php if (! empty($activity['poster_url'])): ?>
            <figure class="gallery-detail-poster">
                <img
                    src="<?= esc($activity['poster_url'], 'attr') ?>"
                    alt="<?= esc($activity['title'] . ' — ' . ucfirst((string) $activity['poster_type']), 'attr') ?>"
                    width="1200"
                    height="800"
                >
                <figcaption><?= esc(ucfirst((string) $activity['poster_type'])) ?> kegiatan</figcaption>
            </figure>
        <?php endif; ?>

        <?php if (! empty($activity['description'])): ?>
            <p class="gallery-detail-description"><?= nl2br(esc($activity['description'])) ?></p>
        <?php endif; ?>

        <?php if (! empty($activity['video_url'])): ?>
            <p class="gallery-detail-video">
                <a href="<?= esc($activity['video_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                    Tonton video kegiatan
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 4h6v6M20 4l-9 9"/><path d="M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/></svg>
                </a>
            </p>
        <?php endif; ?>

        <?php if ($activity['media'] !== []): ?>
            <section class="gallery-detail-media" aria-labelledby="gallery-media-title">
                <h2 id="gallery-media-title">Dokumentasi foto</h2>
                <?php if ($mediaCount > 10): ?>
                    <button
                        class="gallery-detail-viewer-trigger"
                        type="button"
                        aria-haspopup="dialog"
                        aria-controls="gallery-photo-viewer"
                        aria-label="<?= esc('Lihat ' . $mediaCount . ' foto dokumentasi', 'attr') ?>"
                        data-photo-viewer-open
                        data-photo-viewer-title="<?= esc($activity['title'], 'attr') ?>"
                        data-photo-viewer-photos="<?= esc((string) json_encode($viewerPhotos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>"
                        hidden
                    >
                        <img src="<?= esc($activity['media'][0]['url'], 'attr') ?>" alt="" width="240" height="180" loading="lazy">
                        <span class="gallery-detail-viewer-trigger-copy">
                            <strong><?= esc($mediaCount) ?> foto dokumentasi</strong>
                            <span>Buka untuk menelusuri foto</span>
                        </span>
                    </button>

                    <dialog
                        class="gallery-photo-viewer"
                        id="gallery-photo-viewer"
                        aria-labelledby="gallery-photo-viewer-title"
                        data-photo-viewer
                    >
                        <header class="gallery-photo-viewer-header">
                            <h3 id="gallery-photo-viewer-title">Dokumentasi foto</h3>
                            <button class="gallery-photo-viewer-close" type="button" aria-label="Tutup dokumentasi foto" data-photo-viewer-close>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18"/></svg>
                            </button>
                        </header>
                        <div class="gallery-photo-viewer-stage" data-photo-viewer-stage>
                            <button class="gallery-photo-viewer-arrow" type="button" aria-label="Foto sebelumnya" data-photo-viewer-previous>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <img data-photo-viewer-image alt="">
                            <button class="gallery-photo-viewer-arrow" type="button" aria-label="Foto selanjutnya" data-photo-viewer-next>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                        <p class="gallery-photo-viewer-counter" data-photo-viewer-counter aria-live="polite" aria-atomic="true"></p>
                    </dialog>

                    <noscript>
                        <ul class="gallery-detail-photo-links">
                            <?php foreach ($activity['media'] as $index => $media): ?>
                                <li><a href="<?= esc($media['url'], 'attr') ?>"><?= esc('Lihat foto ' . ($index + 1) . ': ' . $media['label']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </noscript>
                    <script src="<?= base_url('assets/js/galeri-viewer.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/galeri-viewer.js') ?>" defer></script>
                <?php else: ?>
                    <div class="gallery-detail-grid">
                        <?php foreach ($activity['media'] as $media): ?>
                            <figure class="gallery-detail-image">
                                <img
                                    src="<?= esc($media['url'], 'attr') ?>"
                                    alt="<?= esc($activity['title'] . ' — ' . $media['label'], 'attr') ?>"
                                    width="1000"
                                    height="750"
                                    loading="lazy"
                                >
                                <figcaption><?= esc($media['label']) ?></figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</article>
<?= $this->endSection() ?>
