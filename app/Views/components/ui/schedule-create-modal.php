<?php
helper('modal');

$scheduleError = $scheduleError ?? '';
$returnTo = $returnTo ?? 'admin/jadwal';
$defaultOpen = $scheduleError !== '';
?>
<?php modal_open(
    'schedule-create-modal',
    $defaultOpen,
    ['aria-labelledby' => 'schedule-create-title', 'aria-describedby' => 'schedule-create-description'],
    ['aria-labelledby' => 'schedule-create-title', 'aria-describedby' => 'schedule-create-description']
) ?>
<?php modal_content_open() ?>
<form class="rm-schedule-form" method="post" action="<?= site_url('admin/jadwal/save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo, 'attr') ?>">

    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Tambah Jadwal', 'fw-semibold', ['id' => 'schedule-create-title'], ['id' => 'schedule-create-title']) ?>
        <?php modal_description('Lengkapi informasi jadwal ibadah.', 'rm-description', ['id' => 'schedule-create-description'], ['id' => 'schedule-create-description']) ?>
    </div>
    <?php modal_close('Kembali', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>

    <?php modal_body_open() ?>
    <?php if ($scheduleError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= esc($scheduleError) ?></div>
    <?php endif; ?>

    <div class="rm-form-grid">
        <div class="rm-field">
            <label for="schedule-create-jenis">Jenis / nama ibadah</label>
            <input class="form-control" id="schedule-create-jenis" name="jenis_ibadah" value="<?= esc(old('jenis_ibadah') ?? '') ?>" required>
        </div>
        <div class="rm-field">
            <label for="schedule-create-tanggal">Tanggal</label>
            <input class="form-control" id="schedule-create-tanggal" name="tanggal" type="date" value="<?= esc(old('tanggal') ?? '') ?>" required>
        </div>
        <div class="rm-field">
            <label for="schedule-create-waktu">Waktu WIT</label>
            <input class="form-control" id="schedule-create-waktu" name="waktu" type="time" value="<?= esc(old('waktu') ?? '') ?>" required>
        </div>
        <div class="rm-field">
            <label for="schedule-create-lokasi">Lokasi</label>
            <input class="form-control" id="schedule-create-lokasi" name="lokasi" value="<?= esc(old('lokasi') ?? '') ?>" required>
        </div>
        <div class="rm-field">
            <label for="schedule-create-pelayan">Nama pelayan (opsional)</label>
            <input class="form-control" id="schedule-create-pelayan" name="pelayan" value="<?= esc(old('pelayan') ?? '') ?>" placeholder="Kosongkan bila belum ditentukan">
        </div>
        <div class="rm-field">
            <label for="schedule-create-status">Status jadwal</label>
            <select class="form-select" id="schedule-create-status" name="is_cancelled">
                <option value="0" <?= (old('is_cancelled') ?? '0') == '0' ? 'selected' : '' ?>>Aktif</option>
                <option value="1" <?= (old('is_cancelled') ?? '0') == '1' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>
    </div>
    <?php modal_section_end() ?>

    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit">Simpan Jadwal</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>
