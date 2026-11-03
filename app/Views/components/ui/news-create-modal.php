<?php
helper('modal');

$newsError = $newsError ?? '';
$returnTo = $returnTo ?? 'admin/berita';
$defaultOpen = ($defaultOpen ?? false) || $newsError !== '';
?>
<?php modal_open(
    'news-create-modal',
    $defaultOpen,
    ['aria-labelledby' => 'news-create-title', 'aria-describedby' => 'news-create-description'],
    ['aria-labelledby' => 'news-create-title', 'aria-describedby' => 'news-create-description']
) ?>
<?php modal_content_open() ?>
<form class="rm-form rm-news-form" method="post" action="<?= site_url('admin/berita/save') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo, 'attr') ?>">

    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Tambah Berita', 'fw-semibold', ['id' => 'news-create-title'], ['id' => 'news-create-title']) ?>
        <?php modal_description('Lengkapi informasi berita.', 'rm-description', ['id' => 'news-create-description'], ['id' => 'news-create-description']) ?>
    </div>
    <?php modal_close('Kembali', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>

    <?php modal_body_open() ?>
    <?php if ($newsError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= esc($newsError) ?></div>
    <?php endif; ?>

    <div class="rm-form-grid">
        <div class="rm-field">
            <label for="news-create-title-input">Judul</label>
            <input class="form-control" id="news-create-title-input" name="title" value="<?= esc(old('title') ?? '') ?>" required>
        </div>
        <div class="rm-field">
            <label for="news-create-slug">Slug</label>
            <input class="form-control" id="news-create-slug" name="slug" value="<?= esc(old('slug') ?? '') ?>" placeholder="otomatis dari judul jika dikosongkan">
        </div>
        <div class="rm-field rm-field-full">
            <label for="news-create-summary">Ringkasan</label>
            <textarea class="form-control" id="news-create-summary" name="summary" rows="3" required><?= esc(old('summary') ?? '') ?></textarea>
        </div>
        <div class="rm-field rm-field-full">
            <label for="news-create-content">Isi berita</label>
            <textarea class="form-control" id="news-create-content" name="content" rows="6" required><?= esc(old('content') ?? '') ?></textarea>
        </div>
        <div class="rm-field">
            <label for="news-create-status">Status publikasi</label>
            <select class="form-select" id="news-create-status" name="status" required>
                <option value="">Pilih status</option>
                <?php foreach ($statusOptions as $option): ?>
                    <option value="<?= esc($option) ?>" <?= (old('status') ?? '') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="rm-field">
            <label for="news-create-cover-image">Gambar kover</label>
            <input class="form-control" id="news-create-cover-image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
        </div>
    </div>
    <?php modal_section_end() ?>

    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit">Simpan Berita</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>
