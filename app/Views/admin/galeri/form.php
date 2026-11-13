<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$isEditing = is_array($item);
$value = static fn(string $field, mixed $fallback = ''): mixed => old($field) ?? ($item[$field] ?? $fallback);
$posterType = (string) $value('poster_type', 'poster');
$status = (string) $value('status', 'Draft');
$description = (string) $value('description');
$eventDate = (string) $value('event_date');
$videoUrl = (string) $value('video_url');
?>
<section class="gallery-admin-page" aria-labelledby="gallery-form-title">
    <header class="gallery-admin-heading">
        <div>
            <h1 id="gallery-form-title"><?= $isEditing ? 'Ubah kegiatan' : 'Tambah kegiatan' ?></h1>
            <p>Judul dan poster wajib diisi; keterangan, tanggal, foto, dan tautan video bersifat opsional.</p>
        </div>
        <a class="button" href="<?= site_url('admin/galeri') ?>">Kembali ke daftar</a>
    </header>

    <?php if (! empty($error)): ?>
        <div class="gallery-admin-alert gallery-admin-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <form class="gallery-admin-form" method="post" action="<?= site_url('admin/galeri/save') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <?php endif; ?>

        <div class="gallery-admin-form-main">
            <label>
                <span>Nama kegiatan <strong aria-hidden="true">*</strong></span>
                <input type="text" name="title" value="<?= esc($value('title'), 'attr') ?>" required maxlength="255" autocomplete="off">
            </label>

            <label>
                <span>Keterangan <small>(opsional)</small></span>
                <textarea name="description" rows="6" maxlength="5000"><?= esc($description) ?></textarea>
            </label>

            <div class="gallery-admin-form-row">
                <label>
                    <span>Tanggal kegiatan <small>(opsional)</small></span>
                    <input type="date" name="event_date" value="<?= esc($eventDate, 'attr') ?>">
                </label>
                <label>
                    <span>Status publikasi</span>
                    <select name="status">
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?= esc($option, 'attr') ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label>
                <span>Tautan video <small>(opsional, URL eksternal)</small></span>
                <input type="url" name="video_url" value="<?= esc($videoUrl, 'attr') ?>" maxlength="2048" placeholder="https://">
            </label>

            <label class="gallery-admin-checkbox">
                <input type="checkbox" name="show_on_home" value="1" <?= (string) $value('show_on_home', '0') === '1' ? 'checked' : '' ?>>
                <span>Pilih untuk ditampilkan di beranda</span>
            </label>
        </div>

        <div class="gallery-admin-form-media">
            <label>
                <span>Jenis gambar utama</span>
                <select name="poster_type">
                    <?php foreach ($posterTypeOptions as $option => $label): ?>
                        <option value="<?= esc($option, 'attr') ?>" <?= $posterType === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <?php if ($isEditing && ! empty($item['poster_path'])): ?>
                <div class="gallery-admin-current-media">
                    <img src="<?= site_url('admin/galeri/media/' . rawurlencode(basename((string) $item['poster_path']))) ?>" alt="Poster saat ini untuk <?= esc($item['title'], 'attr') ?>" width="320" height="220">
                    <p>Poster utama saat ini. Pilih file baru untuk menggantinya.</p>
                </div>
            <?php endif; ?>
            <div class="gallery-admin-file">
                <label class="gallery-admin-file-title" for="gallery-poster-input">Poster atau flayer utama <?= $isEditing ? '<small>(opsional jika tidak diganti)</small>' : '<strong aria-hidden="true">*</strong>' ?></label>
                <label class="gallery-admin-upload-dropzone" data-gallery-upload-dropzone="poster" for="gallery-poster-input">
                    <input id="gallery-poster-input" class="gallery-admin-upload-input" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" <?= $isEditing ? '' : 'required' ?>>
                    <svg class="gallery-admin-upload-icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                        <path d="M24 31V7m0 0-8 8m8-8 8 8M9 29v10h30V29" />
                    </svg>
                    <span class="gallery-admin-upload-copy">
                        <strong>Seret poster ke sini</strong>
                        <span>atau <span class="gallery-admin-upload-action">pilih dari perangkat</span></span>
                    </span>
                </label>
                <small class="gallery-admin-upload-hint">JPG, PNG, atau WEBP; maksimal 10 MB.</small>
                <small class="gallery-admin-upload-hint">Disarankan rasio vertikal 2:3 (contoh: 1200 × 1800 px). Rasio lain tetap dapat diunggah.</small>
                <ul class="gallery-admin-upload-files" data-gallery-upload-files="poster" aria-label="Poster baru yang dipilih" hidden></ul>
                <small class="gallery-admin-upload-status" data-gallery-upload-status="poster" aria-live="polite">Belum ada poster baru dipilih.</small>
            </div>

            <?php if ($media !== []): ?>
                <fieldset class="gallery-admin-existing-photos">
                    <legend>Foto dokumentasi yang tersimpan</legend>
                    <div class="gallery-admin-photo-list">
                        <?php foreach ($media as $photo): ?>
                            <label>
                                <img src="<?= site_url('admin/galeri/media/' . rawurlencode(basename((string) $photo['file_path']))) ?>" alt="Dokumentasi kegiatan: <?= esc(ucfirst((string) $photo['media_type']), 'attr') ?>" width="180" height="130">
                                <span><?= esc(ucfirst((string) $photo['media_type'])) ?></span>
                                <span class="gallery-admin-remove-photo">
                                    <input type="checkbox" name="remove_media[]" value="<?= (int) $photo['id'] ?>">
                                    Hapus foto ini
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>

            <div class="gallery-admin-file">
                <label class="gallery-admin-file-title" for="gallery-photos-input">Tambah foto dokumentasi <small>(opsional, maksimal 30 foto per kegiatan)</small></label>
                <label class="gallery-admin-upload-dropzone" data-gallery-upload-dropzone="photos" for="gallery-photos-input">
                    <input id="gallery-photos-input" class="gallery-admin-upload-input" type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                    <svg class="gallery-admin-upload-icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                        <path d="M24 31V7m0 0-8 8m8-8 8 8M9 29v10h30V29" />
                    </svg>
                    <span class="gallery-admin-upload-copy">
                        <strong>Seret foto ke sini</strong>
                        <span>atau <span class="gallery-admin-upload-action">pilih dari perangkat</span></span>
                    </span>
                </label>
                <small class="gallery-admin-upload-hint">JPG, PNG, atau WEBP; maksimal 10 MB per foto.</small>
                <ul class="gallery-admin-upload-files" data-gallery-upload-files="photos" aria-label="Foto baru yang dipilih" hidden></ul>
                <small class="gallery-admin-upload-status" data-photo-selection-status aria-live="polite"></small>
            </div>
        </div>

        <footer class="gallery-admin-form-actions">
            <a href="<?= site_url('admin/galeri') ?>">Batal</a>
            <button class="button button-primary" type="submit">Simpan kegiatan</button>
        </footer>
    </form>
</section>
<script src="<?= base_url('assets/js/galeri-admin.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/galeri-admin.js') ?>" defer></script>
<?= $this->endSection() ?>
