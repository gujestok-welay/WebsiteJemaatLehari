<?php
$fields = is_array($fields ?? null) ? $fields : [];
$templates = is_array($templates ?? null) ? $templates : [];
$prefix = (string) ($prefix ?? 'content');
$readOnlyFields = is_array($readOnlyFields ?? null) ? $readOnlyFields : [];
$hiddenFields = is_array($hiddenFields ?? null) ? $hiddenFields : [];
?>
<?php foreach ($fields as $key => $value): ?>
    <?php
    $key = (string) $key;
    $fieldTemplate = $templates[$key] ?? null;
    $fieldName = $prefix . '[' . $key . ']';
    $fieldId = 'profile-' . trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $fieldName), '-');
    $label = \App\Libraries\PublicProfileContent::fieldLabel($key);
    $itemLabel = $label;
    $addLabel = strtolower($label);
    if ($prefix === 'territory' && $key === 'title') {
        $label = 'Judul bagian wilayah';
    } elseif ($prefix === 'census' && $key === 'household_suffix') {
        $label = 'Teks setelah jumlah KK';
    } elseif ($prefix === 'census' && $key === 'population_suffix') {
        $label = 'Teks setelah jumlah jiwa';
    } elseif ($prefix === 'territory' && $key === 'boundary_prefix') {
        $label = 'Teks sebelum arah batas';
    } elseif ($prefix === 'territory' && $key === 'sector_prefix') {
        $label = 'Teks sebelum nama sektor';
    } elseif ($prefix === 'territory' && $key === 'unit_suffix') {
        $label = 'Satuan jumlah unit';
    } elseif ($prefix === 'territory' && $key === 'area_share_suffix') {
        $label = 'Teks setelah persentase luas';
    } elseif (str_starts_with($prefix, 'territory[boundaries]') && $key === 'name') {
        $label = 'Nama wilayah';
    } elseif (str_starts_with($prefix, 'territory[sectors]') && $key === 'name') {
        $label = 'Nama sektor';
    } elseif ($prefix === 'territory' && $key === 'orbitations') {
        $label = 'Daftar tujuan';
        $itemLabel = 'Tujuan';
        $addLabel = 'tujuan';
    } elseif ($prefix === 'territory' && $key === 'orbit_title') {
        $label = 'Judul bagian jarak';
    } elseif (str_starts_with($prefix, 'seasons[items]') && $key === 'period') {
        $label = 'Periode musim';
    } elseif ($prefix === 'content[worship]' && $key === 'items') {
        $label = 'Daftar pelayanan';
        $itemLabel = 'Pelayanan';
        $addLabel = 'pelayanan';
    } elseif (str_starts_with($prefix, 'content[worship][items]')) {
        $label = [
            'day' => 'Hari',
            'name' => 'Nama pelayanan',
            'place' => 'Tempat',
            'note' => 'Keterangan',
        ][$key] ?? $label;
    } elseif (str_starts_with($prefix, 'territory[orbitations]') && $key === 'label') {
        $label = 'Tujuan';
    } elseif (str_starts_with($prefix, 'territory[orbitations]') && $key === 'value') {
        $label = 'Jarak';
    } elseif (str_starts_with($prefix, 'territory[boundaries]') && $key === 'icon') {
        $label = 'Arah penunjuk';
    } elseif (str_starts_with($prefix, 'seasons[items]') && $key === 'title') {
        $label = 'Nama musim';
    } elseif (str_starts_with($prefix, 'seasons[items]') && $key === 'description') {
        $label = 'Keterangan musim';
    }
    if ($prefix === 'seasons' && $key === 'items') {
        $label = 'Daftar musim';
        $itemLabel = 'Musim';
        $addLabel = 'musim';
    }
    ?>
    <?php if (in_array($key, $hiddenFields, true)): ?>
        <input type="hidden" name="<?= esc($fieldName, 'attr') ?>" value="<?= esc((string) $value, 'attr') ?>">
    <?php elseif (is_array($value)): ?>
        <?php if (array_is_list($fieldTemplate ?? [])): ?>
            <?php $rowTemplate = $fieldTemplate[0] ?? []; ?>
            <fieldset
                class="profile-content-list"
                data-content-list
                data-next-index="<?= count($value) ?>"
            >
                <legend><?= esc($label) ?></legend>
                <div class="profile-content-list-items">
                    <?php foreach ($value as $index => $item): ?>
                        <div class="profile-content-item" data-content-item>
                            <div class="profile-content-item-heading">
                                <span><?= esc($itemLabel) ?> <?= (int) $index + 1 ?></span>
                                <button class="profile-action-link is-danger" type="button" data-remove-item>Hapus item</button>
                            </div>
                            <?= view('admin/profil/_content-fields', [
                                'fields' => is_array($item) ? $item : [],
                                'templates' => is_array($rowTemplate) ? $rowTemplate : [],
                                'prefix' => $fieldName . '[' . $index . ']',
                                'readOnlyFields' => $readOnlyFields,
                                'hiddenFields' => $hiddenFields,
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <template data-list-template>
                    <div class="profile-content-item" data-content-item>
                        <div class="profile-content-item-heading">
                            <span><?= esc($itemLabel) ?> baru</span>
                            <button class="profile-action-link is-danger" type="button" data-remove-item>Hapus item</button>
                        </div>
                        <?= view('admin/profil/_content-fields', [
                            'fields' => is_array($rowTemplate) ? $rowTemplate : [],
                            'templates' => is_array($rowTemplate) ? $rowTemplate : [],
                            'prefix' => $fieldName . '[__INDEX__]',
                            'readOnlyFields' => $readOnlyFields,
                            'hiddenFields' => $hiddenFields,
                        ]) ?>
                    </div>
                </template>
                <button class="button button-secondary profile-content-add" type="button" data-add-item>
                    Tambah <?= esc($addLabel) ?>
                </button>
            </fieldset>
        <?php else: ?>
            <fieldset class="profile-content-object">
                <legend><?= esc($label) ?></legend>
                <div class="profile-form-grid">
                    <?= view('admin/profil/_content-fields', [
                        'fields' => $value,
                        'templates' => is_array($fieldTemplate) ? $fieldTemplate : [],
                        'prefix' => $fieldName,
                        'readOnlyFields' => $readOnlyFields,
                        'hiddenFields' => $hiddenFields,
                    ]) ?>
                </div>
            </fieldset>
        <?php endif; ?>
    <?php else: ?>
        <?php $readOnly = in_array($fieldName, $readOnlyFields, true); ?>
        <div class="profile-field <?= in_array($key, ['description', 'administration_text', 'topography_text', 'community_text', 'members_empty', 'members_permission_note'], true) ? 'profile-field-wide' : '' ?>">
            <label for="<?= esc($fieldId, 'attr') ?>"><?= esc($label) ?></label>
            <?php if ($key === 'direction' && str_starts_with($prefix, 'territory[boundaries]')): ?>
                <?php
                $directions = ['Utara', 'Timur', 'Selatan', 'Barat'];
                ?>
                <select class="form-select" id="<?= esc($fieldId, 'attr') ?>" name="<?= esc($fieldName, 'attr') ?>" required>
                    <?php if (! in_array((string) $value, $directions, true)): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" selected>Arah saat ini: <?= esc((string) $value) ?></option>
                    <?php endif; ?>
                    <?php foreach ($directions as $direction): ?>
                        <option value="<?= esc($direction, 'attr') ?>" <?= $value === $direction ? 'selected' : '' ?>><?= esc($direction) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($key === 'icon' && str_starts_with($prefix, 'territory[boundaries]')): ?>
                <input type="hidden" id="<?= esc($fieldId, 'attr') ?>" name="<?= esc($fieldName, 'attr') ?>" value="<?= esc((string) $value, 'attr') ?>" data-boundary-icon>
            <?php elseif (is_bool($value) || is_bool($fieldTemplate)): ?>
                <input type="hidden" name="<?= esc($fieldName, 'attr') ?>" value="0">
                <div class="profile-toggle">
                    <input
                        id="<?= esc($fieldId, 'attr') ?>"
                        name="<?= esc($fieldName, 'attr') ?>"
                        type="checkbox"
                        value="1"
                        <?= $value ? 'checked' : '' ?>
                    >
                    <span>Tampilkan hanya setelah izin publikasi dipastikan</span>
                </div>
            <?php elseif (is_int($value) || is_float($value) || is_int($fieldTemplate) || is_float($fieldTemplate)): ?>
                <input
                    class="form-control"
                    id="<?= esc($fieldId, 'attr') ?>"
                    name="<?= esc($fieldName, 'attr') ?>"
                    type="number"
                    min="0"
                    step="<?= is_float($fieldTemplate) || is_float($value) ? '0.01' : '1' ?>"
                    value="<?= esc((string) $value, 'attr') ?>"
                    required
                >
            <?php elseif ($key === 'target'): ?>
                <select class="form-select" id="<?= esc($fieldId, 'attr') ?>" name="<?= esc($fieldName, 'attr') ?>" required>
                    <option value="wilayah" <?= $value === 'wilayah' ? 'selected' : '' ?>>Wilayah</option>
                    <option value="jemaat" <?= $value === 'jemaat' ? 'selected' : '' ?>>Data Jemaat</option>
                    <option value="ibadah" <?= $value === 'ibadah' ? 'selected' : '' ?>>Peribadahan</option>
                </select>
            <?php elseif (in_array($key, ['description', 'administration_text', 'topography_text', 'community_text', 'productive_age_note', 'members_empty', 'members_permission_note', 'meta_description', 'preview_notice_body'], true) || mb_strlen((string) $value) > 110): ?>
                <textarea
                    class="form-control"
                    id="<?= esc($fieldId, 'attr') ?>"
                    name="<?= esc($fieldName, 'attr') ?>"
                    rows="3"
                    maxlength="3000"
                ><?= esc((string) $value) ?></textarea>
            <?php else: ?>
                <input
                    class="form-control"
                    id="<?= esc($fieldId, 'attr') ?>"
                    name="<?= esc($fieldName, 'attr') ?>"
                    value="<?= esc((string) $value, 'attr') ?>"
                    <?= $prefix === 'census' && in_array($key, ['household_suffix', 'population_suffix'], true) ? 'aria-describedby="' . esc($fieldId . '-hint', 'attr') . '"' : '' ?>
                    <?= $prefix === 'territory' && in_array($key, ['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true) ? 'aria-describedby="' . esc($fieldId . '-hint', 'attr') . '"' : '' ?>
                    <?= $readOnly ? 'readonly aria-describedby="' . esc($fieldId . '-note', 'attr') . '"' : '' ?>
                    maxlength="3000"
                >
            <?php endif; ?>
            <?php if ($prefix === 'territory' && in_array($key, ['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true)): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldId . '-hint', 'attr') ?>">
                    <?php if ($key === 'boundary_prefix'): ?>Ditampilkan sebelum arah mata angin, misalnya “Sebelah Utara”.
                    <?php elseif ($key === 'sector_prefix'): ?>Ditampilkan sebelum nama sektor, misalnya “Sektor Koinonia”.
                    <?php elseif ($key === 'unit_suffix'): ?>Ditampilkan setelah jumlah unit, misalnya “3 unit”.
                    <?php else: ?>Ditampilkan setelah persentase luas, misalnya “64% dari total wilayah”.
                    <?php endif; ?>
                </small>
            <?php endif; ?>
            <?php if ($prefix === 'census' && in_array($key, ['household_suffix', 'population_suffix'], true)): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldId . '-hint', 'attr') ?>">Kata ini muncul setelah angka di halaman publik. Contoh: <?= $key === 'household_suffix' ? 'KK dan' : 'jiwa.' ?></small>
            <?php endif; ?>
            <?php if ($readOnly): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldId . '-note', 'attr') ?>">Dihitung dari modul jumlah KK dan jiwa.</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
