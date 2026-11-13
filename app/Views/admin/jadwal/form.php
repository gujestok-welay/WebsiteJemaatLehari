<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $sedangMengubah = ! empty($item); ?>
<section class="schedule-form-page" aria-labelledby="schedule-form-title">
    <header class="schedule-page-heading">
        <h1 id="schedule-form-title"><?= $sedangMengubah ? 'Ubah jadwal' : 'Tambah jadwal' ?></h1>
        <a class="button" href="<?= site_url('admin/jadwal') ?>">Kembali ke jadwal</a>
    </header>

    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="schedule-form-panel" aria-label="Formulir jadwal ibadah">
        <p class="schedule-form-intro">Isi informasi jadwal sesuai data yang sudah ditetapkan.</p>
        <form method="post" action="<?= site_url('admin/jadwal/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc(old('id') ?? ($item['id'] ?? '')) ?>">

            <div class="schedule-form-grid">
                <div class="schedule-form-field">
                    <label for="jenis_ibadah">Jenis atau nama ibadah</label>
                    <input class="form-control" id="jenis_ibadah" name="jenis_ibadah" value="<?= esc(old('jenis_ibadah') ?? ($item['jenis_ibadah'] ?? '')) ?>" maxlength="120" required>
                </div>
                <div class="schedule-form-field">
                    <label for="tanggal">Tanggal</label>
                    <input class="form-control" id="tanggal" name="tanggal" type="date" value="<?= esc(old('tanggal') ?? ($item['tanggal'] ?? '')) ?>" required>
                </div>
                <div class="schedule-form-field">
                    <label for="waktu">Waktu (WIT)</label>
                    <input class="form-control" id="waktu" name="waktu" type="time" value="<?= esc(old('waktu') ?? ($item['waktu'] ?? '')) ?>" required>
                </div>
                <div class="schedule-form-field">
                    <label for="lokasi">Lokasi</label>
                    <input class="form-control" id="lokasi" name="lokasi" value="<?= esc(old('lokasi') ?? ($item['lokasi'] ?? '')) ?>" maxlength="255" required>
                </div>
                <div class="schedule-form-field">
                    <label for="pelayan">Pelayan ibadah <span>(opsional)</span></label>
                    <input class="form-control" id="pelayan" name="pelayan" value="<?= esc(old('pelayan') ?? ($item['pelayan'] ?? '')) ?>" maxlength="255" placeholder="Kosongkan bila belum ditentukan">
                </div>
                <div class="schedule-form-field">
                    <label for="keterangan">Keterangan <span>(opsional)</span></label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="4" maxlength="2000"><?= esc(old('keterangan') ?? ($item['keterangan'] ?? '')) ?></textarea>
                </div>
                <div class="schedule-form-field">
                    <label for="is_cancelled">Status jadwal</label>
                    <?php $statusJadwal = old('is_cancelled') ?? ($item['is_cancelled'] ?? 0); ?>
                    <select class="form-select" id="is_cancelled" name="is_cancelled">
                        <option value="0" <?= (int) $statusJadwal === 0 ? 'selected' : '' ?>>Aktif</option>
                        <option value="1" <?= (int) $statusJadwal === 1 ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="schedule-form-actions">
                <button class="button button-primary" type="submit"><?= $sedangMengubah ? 'Simpan perubahan' : 'Simpan jadwal' ?></button>
                <a class="button" href="<?= site_url('admin/jadwal') ?>">Batal</a>
            </div>
        </form>
    </section>
</section>
<?= $this->endSection() ?>
