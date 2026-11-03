<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $sedangMengubah = ! empty($editItem); ?>
<section class="statistics-page" aria-labelledby="statistics-page-title">
    <header class="statistics-page-heading">
        <div>
            <h1 id="statistics-page-title">Statistik Agregat</h1>
            <p>Kelola angka jemaat yang sudah diverifikasi. Jangan masukkan data pribadi atau rincian individu.</p>
        </div>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="statistics-form-section" aria-labelledby="statistics-form-title">
        <div class="statistics-section-heading">
            <h2 id="statistics-form-title"><?= $sedangMengubah ? 'Ubah statistik' : 'Tambah statistik' ?></h2>
            <p>Gunakan satuan atau keterangan untuk memberi konteks pada nilai.</p>
        </div>
        <form method="post" action="<?= site_url('admin/statistik/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc(old('id') ?? ($editItem['id'] ?? '')) ?>">
            <div class="statistics-form-grid">
                <div class="statistics-field">
                    <label for="metric_label">Label metrik</label>
                    <input class="form-control" id="metric_label" name="metric_label" value="<?= esc(old('metric_label') ?? ($editItem['metric_label'] ?? ''), 'attr') ?>" maxlength="150" required>
                </div>
                <div class="statistics-field">
                    <label for="metric_value">Nilai</label>
                    <input class="form-control" id="metric_value" name="metric_value" type="number" step="0.01" value="<?= esc(old('metric_value') ?? ($editItem['metric_value'] ?? ''), 'attr') ?>" required>
                </div>
                <div class="statistics-field statistics-field-wide">
                    <label for="unit_label">Satuan atau keterangan <span>(opsional)</span></label>
                    <input class="form-control" id="unit_label" name="unit_label" value="<?= esc(old('unit_label') ?? ($editItem['unit_label'] ?? ''), 'attr') ?>" maxlength="80">
                </div>
            </div>
            <div class="statistics-form-actions">
                <button class="button button-primary" type="submit"><?= $sedangMengubah ? 'Simpan perubahan' : 'Simpan statistik' ?></button>
                <?php if ($sedangMengubah): ?>
                    <a class="button" href="<?= site_url('admin/statistik') ?>">Batal mengubah</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="statistics-list-section" aria-labelledby="statistics-list-title">
        <div class="statistics-section-heading">
            <h2 id="statistics-list-title">Data statistik tersimpan</h2>
            <p><?= number_format(count($items), 0, ',', '.') ?> metrik</p>
        </div>

        <?php if (empty($items)): ?>
            <div class="statistics-empty">
                <h3>Belum ada data statistik</h3>
                <p>Data akan muncul di sini setelah informasi agregat yang terverifikasi disimpan.</p>
            </div>
        <?php else: ?>
            <div class="statistics-table-wrap">
                <table class="statistics-table">
                    <thead>
                    <tr>
                        <th scope="col">Label</th>
                        <th scope="col">Nilai</th>
                        <th scope="col">Satuan / keterangan</th>
                        <th scope="col">Terakhir diperbarui</th>
                        <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="statistics-label"><?= esc($item['metric_label'] ?? '-') ?></td>
                            <td class="statistics-value"><?= esc(number_format((float) ($item['metric_value'] ?? 0), 2, ',', '.')) ?></td>
                            <td><?= esc($item['unit_label'] ?? '-') ?></td>
                            <td class="statistics-updated"><?= esc($item['updated_at'] ?? '-') ?></td>
                            <td>
                                <div class="statistics-actions">
                                    <a class="statistics-action-link" href="<?= site_url('admin/statistik?edit=' . (int) ($item['id'] ?? 0)) ?>">Ubah</a>
                                    <form method="post" action="<?= site_url('admin/statistik/delete/' . (int) ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Hapus data statistik ini?');">
                                        <?= csrf_field() ?>
                                        <button class="statistics-action-link is-danger" type="submit">Hapus</button>
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
</section>
<?= $this->endSection() ?>
