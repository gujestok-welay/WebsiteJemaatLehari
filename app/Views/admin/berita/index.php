<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="news-page" aria-labelledby="news-page-title">
    <header class="news-page-heading">
        <h1 id="news-page-title">Berita</h1>
        <a class="button button-primary" href="<?= site_url('admin/berita/create') ?>" data-rm-open="#news-create-modal">Tambah berita</a>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <!-- impeccable-variants-start fd2e5c18 -->
    <div data-impeccable-variants="fd2e5c18" data-impeccable-variant-count="3" style="display: contents">
      <!-- Original -->
      <div data-impeccable-variant="original">
        <section class="news-panel" aria-label="Daftar berita">
            <div class="news-toolbar">
                <p class="news-result-count"><?= count($items) ?> berita</p>
                <form class="news-search" method="get" action="<?= site_url('admin/berita') ?>" role="search">
                    <input type="search" name="q" value="<?= esc($term ?? '', 'attr') ?>" placeholder="Cari judul berita" aria-label="Cari judul berita">
                    <select name="status" aria-label="Filter status berita">
                        <option value="">Semua status</option>
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?= esc($option) ?>" <?= ($status ?? '') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button" type="submit">Cari</button>
                    <?php if (($term ?? '') !== '' || ($status ?? '') !== ''): ?>
                        <a class="news-reset" href="<?= site_url('admin/berita') ?>">Hapus filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($items)): ?>
                <div class="news-empty">
                    <h2><?= ($term ?? '') !== '' || ($status ?? '') !== '' ? 'Tidak ada berita yang cocok' : 'Belum ada berita' ?></h2>
                    <p><?= ($term ?? '') !== '' || ($status ?? '') !== '' ? 'Coba ubah kata pencarian atau status berita.' : 'Berita yang disimpan akan tampil di sini.' ?></p>
                    <?php if (($term ?? '') === '' && ($status ?? '') === ''): ?>
                        <a class="text-link" href="<?= site_url('admin/berita/create') ?>" data-rm-open="#news-create-modal">Tulis berita pertama</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="news-table-wrap">
                    <table class="news-table">
                        <thead>
                        <tr>
                            <th scope="col">Judul</th>
                            <th scope="col">Status</th>
                            <th scope="col">Ringkasan</th>
                            <th scope="col" class="news-updated-column">Pembaruan</th>
                            <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php $statusClass = strtolower(str_replace(' ', '-', (string) ($item['status'] ?? 'Draft'))); ?>
                            <tr>
                                <td class="news-title-cell">
                                    <strong><?= esc($item['title'] ?? '') ?></strong>
                                    <span>/<?= esc($item['slug'] ?? '') ?></span>
                                </td>
                                <td><span class="news-status news-status-<?= esc($statusClass, 'attr') ?>"><?= esc($item['status'] ?? 'Draft') ?></span></td>
                                <td class="news-summary"><?= esc($item['summary'] ?? '') ?></td>
                                <td class="news-updated-column"><?= esc($item['updated_at'] ?? $item['created_at'] ?? '-') ?></td>
                                <td>
                                    <div class="news-actions">
                                        <?php if (($item['status'] ?? '') === 'Arsip'): ?>
                                            <form method="post" action="<?= site_url('admin/berita/restore/' . (int) ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Pulihkan berita ini?');">
                                                <?= csrf_field() ?>
                                                <button class="news-action-link news-action-labeled" type="submit">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                        <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                                                        <path d="M3 3v5h5M12 8v4l3 2" />
                                                    </svg>
                                                    <span>Pulihkan</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button
                                                class="news-action-link"
                                                type="button"
                                                aria-label="Ubah berita: <?= esc($item['title'] ?? '', 'attr') ?>"
                                                data-rm-open="#news-create-modal"
                                                data-news-edit="<?= esc(json_encode([
                                                    'id' => (int) ($item['id'] ?? 0),
                                                    'title' => (string) ($item['title'] ?? ''),
                                                    'slug' => (string) ($item['slug'] ?? ''),
                                                    'summary' => (string) ($item['summary'] ?? ''),
                                                    'content' => (string) ($item['content'] ?? ''),
                                                    'status' => (string) ($item['status'] ?? ''),
                                                    'cover_image' => (string) ($item['cover_image'] ?? ''),
                                                ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), 'attr') ?>"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                    <path d="M12 20h9" />
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                                </svg>
                                            </button>
                                            <form method="post" action="<?= site_url('admin/berita/archive/' . (int) ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Arsipkan berita ini?');">
                                                <?= csrf_field() ?>
                                                <button
                                                    class="news-action-link news-action-archive"
                                                    type="submit"
                                                    aria-label="Arsipkan berita: <?= esc($item['title'] ?? '', 'attr') ?>"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                        <path d="M3 4h18v4H3z" />
                                                        <path d="M5 8v11h14V8M10 12h4" />
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
      </div>
      <!-- Variants: insert below this line -->
    </div>
    <!-- impeccable-variants-end fd2e5c18 -->
    <span class="visually-hidden" aria-live="polite"><?= count($items) ?> berita ditemukan.</span>
</section>
<?= $this->include('components/ui/news-create-modal', [
    'newsError' => $newsError ?? '',
    'returnTo' => 'admin/berita',
    'defaultOpen' => $newsModalOpen ?? false,
    'formStatusOptions' => $formStatusOptions,
]) ?>
<?= $this->endSection() ?>
