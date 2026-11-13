<?php
helper('modal');

$newsError = $newsError ?? '';
$returnTo = $returnTo ?? 'admin/berita';
$isEditing = (string) (old('id') ?? '') !== '';
$defaultOpen = ($defaultOpen ?? false) || $newsError !== '';
?>
<?php modal_open(
    'news-create-modal',
    $defaultOpen,
    ['aria-labelledby' => 'news-create-title', 'aria-describedby' => 'news-create-description'],
    ['aria-labelledby' => 'news-create-title', 'aria-describedby' => 'news-create-description']
) ?>
<?php modal_content_open() ?>
<form class="rm-form rm-news-form" method="post" action="<?= site_url('admin/berita/save') ?>" enctype="multipart/form-data" data-news-form data-news-metadata-url="<?= esc(site_url('admin/berita/metadata'), 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo, 'attr') ?>">
    <input type="hidden" id="news-record-id" name="id" value="<?= esc(old('id') ?? '', 'attr') ?>">
    <input type="hidden" name="source_image_url" value="" data-news-source-image-url>

    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title($isEditing ? 'Edit Berita' : 'Tambah Berita', 'fw-semibold', ['id' => 'news-create-title'], ['id' => 'news-create-title']) ?>
        <?php modal_description($isEditing ? 'Perbarui informasi berita.' : 'Lengkapi informasi berita.', 'rm-description', ['id' => 'news-create-description'], ['id' => 'news-create-description']) ?>
    </div>
    <?php modal_close('Kembali', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>

    <?php modal_body_open() ?>
    <?php if ($newsError !== ''): ?>
        <div class="alert alert-danger" role="alert" data-news-error><?= esc($newsError) ?></div>
    <?php endif; ?>

    <div class="rm-form-grid">
        <div class="rm-field">
            <label for="news-create-type">Jenis berita</label>
            <select class="form-select" id="news-create-type" name="article_type" required data-news-type>
                <option value="internal" <?= (old('article_type') ?? 'internal') === 'internal' ? 'selected' : '' ?>>Berita Jemaat</option>
                <option value="external" <?= (old('article_type') ?? '') === 'external' ? 'selected' : '' ?>>Berita dari sumber lain</option>
            </select>
        </div>
        <div class="rm-field rm-field-full" data-news-source-url-field>
            <label for="news-source-url">Tautan berita asli</label>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <input class="form-control" id="news-source-url" name="source_url" type="url" maxlength="2048" value="<?= esc(old('source_url') ?? '', 'attr') ?>" placeholder="https://..." data-news-source-url>
                <button class="button flex-shrink-0" type="button" data-news-fetch-metadata>Ambil judul dan sumber dari tautan</button>
            </div>
            <small class="form-text">Metadata diambil dari halaman publik. Jika tidak tersedia, isi judul dan sumber secara manual.</small>
            <p class="form-text mb-0" data-news-metadata-status role="status" aria-live="polite"></p>
        </div>
        <div class="rm-field" data-news-source-name-field>
            <label for="news-source-name">Nama sumber</label>
            <input class="form-control" id="news-source-name" name="source_name" maxlength="255" value="<?= esc(old('source_name') ?? '', 'attr') ?>" placeholder="Contoh: Sinode GPM" data-news-source-name>
        </div>
        <div class="rm-field">
            <label for="news-create-title-input">Judul</label>
            <textarea class="form-control rm-news-title-field" id="news-create-title-input" name="title" rows="1" minlength="3" maxlength="255" required data-news-title><?= esc(old('title') ?? '') ?></textarea>
        </div>
        <div class="rm-field">
            <label for="news-create-slug">Slug</label>
            <input class="form-control" id="news-create-slug" name="slug" value="<?= esc(old('slug') ?? '') ?>" maxlength="255" placeholder="otomatis dari judul jika dikosongkan">
        </div>
        <div class="rm-field rm-field-full">
            <label for="news-create-summary">Ringkasan</label>
            <textarea class="form-control" id="news-create-summary" name="summary" rows="3" maxlength="500" required><?= esc(old('summary') ?? '') ?></textarea>
            <small class="form-text" data-news-summary-help hidden>Ringkasan dari metadata sumber terisi sebagai draf. Tinjau dan sunting sebelum diterbitkan.</small>
        </div>
        <div class="rm-field rm-field-full" data-news-content-field>
            <label for="news-create-content">Isi berita</label>
            <textarea class="form-control" id="news-create-content" name="content" rows="6"><?= esc(old('content') ?? '') ?></textarea>
            <small class="form-text">Untuk berita dari sumber lain, tulis ringkasan dengan kata-kata sendiri. Jangan salin isi artikel sumber.</small>
        </div>
        <div class="rm-field">
            <label for="news-create-status">Status publikasi</label>
            <select class="form-select" id="news-create-status" name="status" required>
                <option value="">Pilih status</option>
                <?php foreach ($formStatusOptions as $option): ?>
                    <option value="<?= esc($option) ?>" <?= (old('status') ?? '') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="rm-field rm-news-cover-field">
            <label class="gallery-admin-file-title" for="news-create-cover-image">Gambar kover</label>
            <div class="gallery-admin-file">
                <label class="gallery-admin-upload-dropzone" data-news-cover-dropzone for="news-create-cover-image">
                    <input class="gallery-admin-upload-input" id="news-create-cover-image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
                    <svg class="gallery-admin-upload-icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                        <path d="M24 31V7m0 0-8 8m8-8 8 8M9 29v10h30V29" />
                    </svg>
                    <span class="gallery-admin-upload-copy">
                        <strong>Seret gambar kover ke sini</strong>
                        <span>atau <span class="gallery-admin-upload-action">pilih dari perangkat</span></span>
                    </span>
                </label>
                <small class="gallery-admin-upload-hint">JPG, PNG, atau WEBP. Maksimal 10 MB.</small>
                <ul class="gallery-admin-upload-files" data-news-cover-preview aria-label="Gambar kover yang dipilih" aria-live="polite" hidden></ul>
                <div class="news-source-image-preview" data-news-source-image-preview hidden>
                    <figure class="m-0" data-news-source-image-figure></figure>
                    <label class="form-check news-source-image-consent">
                        <input class="form-check-input" type="checkbox" name="use_source_image" value="1" data-news-use-source-image>
                        <span class="form-check-label">Gunakan gambar ini sebagai kover. Pastikan penggunaannya diizinkan.</span>
                    </label>
                </div>
            </div>
            <div class="form-text text-danger" data-news-file-error role="alert" hidden></div>
            <div class="form-text" data-news-current-cover hidden>Gambar kover yang ada akan dipertahankan jika tidak memilih gambar baru.</div>
        </div>
    </div>
    <?php modal_section_end() ?>

    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-news-submit><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Berita' ?></button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>
