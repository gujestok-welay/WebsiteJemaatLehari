<?php
$categoryName = (string) ($item['category'] ?? 'Berita');
$articleId = 'berita-' . (string) $item['id'];
$articleUrl = (string) ($item['url'] ?? site_url('berita/' . ($item['slug'] ?? $item['id'])));
$isExternal = ($item['category'] ?? '') === 'Berita eksternal';
$bgImage = ! empty($item['image'])
    ? (string) $item['image']
    : ($isExternal ? null : base_url('assets/images/kegiatan/berita' . (($index % 3) + 1) . '.jpeg'));
?>
<article id="<?= esc($articleId, 'attr') ?>" class="card berita-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
    <a href="<?= esc($articleUrl, 'attr') ?>" class="berita-thumb d-block" aria-label="Buka <?= esc($item['title'], 'attr') ?>">
        <?php if ($bgImage !== null): ?>
            <span class="berita-thumb-bg" style="background-image:url('<?= esc($bgImage, 'attr') ?>');"></span>
        <?php endif; ?>
        <span class="berita-chip"><?= esc($categoryName) ?></span>
    </a>

    <div class="card-body p-4 d-flex flex-column">
        <div class="berita-meta small text-body-secondary d-flex flex-wrap gap-3 mb-3">
            <?php if (! empty($isSample) || ! empty($item['is_sample'])): ?>
                <span class="sample-content-badge">Contoh</span>
            <?php endif; ?>
            <span><i class="bi bi-calendar3 me-1"></i><?= esc($item['date']) ?></span>
            <span><?= esc(($item['category'] ?? '') === 'Berita eksternal' ? ($item['source_name'] ?? 'Sumber eksternal') : 'Media Center') ?></span>
        </div>

        <h2 class="berita-judul h4 fw-bold mb-3">
            <?= view('components/css-link', [
                'url' => $articleUrl,
                'label' => $item['title'],
                'className' => 'stretched-link text-decoration-none',
                'icon' => '',
            ]) ?>
        </h2>

        <p class="text-body-secondary mb-0 berita-ringkas"><?= esc($item['summary']) ?></p>
    </div>
</article>
