<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="news-page" aria-labelledby="news-page-title">
    <header class="news-page-heading">
        <h1 id="news-page-title">Berita</h1>
        <a class="button button-primary" href="<?= site_url('admin/berita/create') ?>">Tambah berita</a>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

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
                    <a class="text-link" href="<?= site_url('admin/berita/create') ?>">Tulis berita pertama</a>
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
                                    <a class="news-action-link" href="<?= site_url('admin/berita/edit/' . (int) ($item['id'] ?? 0)) ?>">Ubah</a>
                                    <form method="post" action="<?= site_url('admin/berita/delete/' . (int) ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Arsipkan berita ini?');">
                                        <?= csrf_field() ?>
                                        <button class="news-action-link news-action-archive" type="submit">Arsipkan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
    <span class="visually-hidden" aria-live="polite"><?= count($items) ?> berita ditemukan.</span>
</section>
<?= $this->endSection() ?>
