<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="gallery-admin-page" aria-labelledby="gallery-admin-title">
    <header class="gallery-admin-heading">
        <div>
            <h1 id="gallery-admin-title">Galeri kegiatan</h1>
            <p>Kelola poster, foto dokumentasi, dan tautan video kegiatan.</p>
        </div>
        <a class="button button-primary" href="<?= site_url('admin/galeri/create') ?>">Tambah kegiatan</a>
    </header>

    <?php if (! empty($success)): ?>
        <div class="gallery-admin-alert gallery-admin-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="gallery-admin-alert gallery-admin-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="gallery-admin-panel" aria-label="Daftar kegiatan">
        <div class="gallery-admin-toolbar">
            <p><?= count($items) ?> kegiatan</p>
            <form method="get" action="<?= site_url('admin/galeri') ?>" role="search">
                <label class="visually-hidden" for="gallery-search">Cari kegiatan</label>
                <input id="gallery-search" type="search" name="q" value="<?= esc($term, 'attr') ?>" placeholder="Cari kegiatan">
                <label class="visually-hidden" for="gallery-status">Status publikasi</label>
                <select id="gallery-status" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusOptions as $option): ?>
                        <option value="<?= esc($option, 'attr') ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($term !== '' || $status !== ''): ?>
                    <a href="<?= site_url('admin/galeri') ?>">Hapus filter</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($items === []): ?>
            <div class="gallery-admin-empty">
                <h2><?= $term !== '' || $status !== '' ? 'Tidak ada kegiatan yang cocok' : 'Belum ada kegiatan' ?></h2>
                <p><?= $term !== '' || $status !== '' ? 'Coba ubah pencarian atau status publikasi.' : 'Poster dan dokumentasi yang disimpan akan tercatat di sini.' ?></p>
                <?php if ($term === '' && $status === ''): ?>
                    <a href="<?= site_url('admin/galeri/create') ?>">Tambah kegiatan pertama</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="gallery-admin-table-wrap">
                <table class="gallery-admin-table">
                    <thead>
                    <tr>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Status</th>
                        <th scope="col">Beranda</th>
                        <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="gallery-admin-cell-label" aria-hidden="true">Kegiatan</span>
                                <strong><?= esc($item['title']) ?></strong>
                                <span><?= esc(ucfirst((string) $item['poster_type'])) ?></span>
                            </td>
                            <td>
                                <span class="gallery-admin-cell-label" aria-hidden="true">Tanggal</span>
                                <?= esc($item['event_date'] ?: '—') ?>
                            </td>
                            <td>
                                <span class="gallery-admin-cell-label" aria-hidden="true">Status</span>
                                <span class="gallery-admin-status gallery-admin-status-<?= esc(strtolower((string) $item['status']), 'attr') ?>"><?= esc($item['status']) ?></span>
                            </td>
                            <td>
                                <span class="gallery-admin-cell-label" aria-hidden="true">Beranda</span>
                                <?= ! empty($item['show_on_home']) ? 'Dipilih' : '—' ?>
                            </td>
                            <td>
                                <span class="gallery-admin-cell-label" aria-hidden="true">Tindakan</span>
                                <div class="gallery-admin-actions">
                                    <?php if ($item['status'] === 'Arsip'): ?>
                                        <form method="post" action="<?= site_url('admin/galeri/restore/' . (int) $item['id']) ?>">
                                            <?= csrf_field() ?>
                                            <button class="news-action-link gallery-admin-labeled-action" type="submit">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                                                    <path d="M3 3v5h5M12 8v4l3 2" />
                                                </svg>
                                                <span>Pulihkan</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a class="news-action-link gallery-admin-labeled-action" href="<?= site_url('admin/galeri/edit/' . (int) $item['id']) ?>">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                            </svg>
                                            <span>Edit</span>
                                        </a>
                                        <form method="post" action="<?= site_url('admin/galeri/archive/' . (int) $item['id']) ?>" onsubmit="return confirm('Arsipkan kegiatan ini?');">
                                            <?= csrf_field() ?>
                                            <button class="news-action-link news-action-archive gallery-admin-labeled-action" type="submit">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M3 4h18v4H3z" />
                                                    <path d="M5 8v11h14V8M10 12h4" />
                                                </svg>
                                                <span>Arsipkan</span>
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
</section>
<?= $this->endSection() ?>
