<?php
$fields = is_array($fields ?? null) ? $fields : [];
$templates = is_array($templates ?? null) ? $templates : [];
$prefix = (string) ($prefix ?? 'content');
$readOnlyFields = is_array($readOnlyFields ?? null) ? $readOnlyFields : [];
$hiddenFields = is_array($hiddenFields ?? null) ? $hiddenFields : [];
$objectLabels = is_array($objectLabels ?? null) ? $objectLabels : [];
?>
<?php foreach ($fields as $key => $value): ?>
    <?php
    $key = (string) $key;
    $fieldTemplate = $templates[$key] ?? null;
    $fieldName = $prefix . '[' . $key . ']';
    $fieldId = 'profile-' . trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $fieldName), '-');
    $readOnly = in_array($fieldName, $readOnlyFields, true);
    $label = \App\Libraries\PublicProfileContent::fieldLabel($key);
    $itemLabel = $label;
    $addLabel = strtolower($label);
    $fieldHelpText = match ($prefix . '[' . $key . ']') {
        'content[hero][page_title]' => 'Judul yang tampil pada tab browser; berbeda dari judul besar di halaman Profil.',
        'content[hero][meta_description]' => 'Ringkasan yang dapat muncul di hasil mesin pencari, bukan paragraf utama Profil.',
        'content[hero][stats_aria_label]' => 'Nama ringkasan yang dibacakan pembaca layar sebelum daftar angka; tidak tampil sebagai teks visual.',
        'content[hero][map_link_label]' => 'Teks ini melengkapi awalan “Lihat lokasi di” pada tautan Google Maps.',
        'content[leadership][show_members]' => 'Jika aktif, nama dan jabatan anggota Majelis tampil pada halaman Profil.',
        default => '',
    };
    $fieldHintId = $fieldId . '-hint';
    $fieldHelpId = $fieldId . '-help';
    $fieldDescribedBy = [];
    if ($fieldHelpText !== '') {
        $fieldDescribedBy[] = $fieldHelpId;
    }
    if ($readOnly) {
        $fieldDescribedBy[] = $fieldId . '-note';
    }
    if (
        ($prefix === 'census' && in_array($key, ['household_suffix', 'population_suffix', 'legend_male', 'legend_female', 'count_separator', 'age_unit'], true))
        || ($prefix === 'territory' && in_array($key, ['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true))
    ) {
        $fieldDescribedBy[] = $fieldHintId;
    }
    $ariaDescribedBy = $fieldDescribedBy === []
        ? ''
        : ' aria-describedby="' . esc(implode(' ', $fieldDescribedBy), 'attr') . '"';
    if ($prefix === 'territory' && $key === 'title') {
        $label = 'Judul bagian wilayah';
    } elseif ($prefix === 'census' && $key === 'household_suffix') {
        $label = 'Tulisan setelah jumlah KK';
    } elseif ($prefix === 'census' && $key === 'population_suffix') {
        $label = 'Tulisan setelah jumlah jiwa';
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
            <?php $ageRangeList = $prefix === 'census' && $key === 'age_rows'; ?>
            <?php
            $territoryListType = '';
            if ($prefix === 'territory' && in_array($key, ['boundaries', 'sectors', 'orbitations'], true)) {
                $territoryListType = $key;
            } elseif ($prefix === 'seasons' && $key === 'items') {
                $territoryListType = 'seasons';
            }
            ?>
            <fieldset
                class="profile-content-list"
                data-content-list
                <?= $ageRangeList ? 'data-census-age-list' : '' ?>
                <?= $territoryListType !== '' ? 'data-territory-item-list="' . esc($territoryListType, 'attr') . '"' : '' ?>
                data-next-index="<?= count($value) ?>"
            >
                <legend><?= esc($label) ?></legend>
                <div class="profile-content-list-items">
                    <?php foreach ($value as $index => $item): ?>
                        <div class="profile-content-item" data-content-item>
                            <?php
                            $ageLabelFieldName = $ageRangeList ? $fieldName . '[' . $index . '][label]' : '';
                            $ageLabelFieldId = $ageRangeList
                                ? 'profile-' . trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $ageLabelFieldName), '-')
                                : '';
                            $territorySummary = match ($territoryListType) {
                                'boundaries' => trim((string) ($item['direction'] ?? '') . ' — ' . (string) ($item['name'] ?? ''), ' —'),
                                'sectors' => trim((string) ($item['name'] ?? '')),
                                'orbitations' => trim((string) ($item['label'] ?? '') . ' — ' . (string) ($item['value'] ?? ''), ' —'),
                                'seasons' => trim((string) ($item['period'] ?? '') . ' · ' . (string) ($item['title'] ?? ''), ' ·'),
                                default => '',
                            };
                            ?>
                            <div class="profile-content-item-heading">
                                <?php if ($territoryListType !== ''): ?>
                                    <span data-territory-item-summary><?= esc($territorySummary) ?></span>
                                    <button class="profile-action-link" type="button" data-territory-item-toggle aria-expanded="false">
                                        Ubah
                                    </button>
                                    <button class="profile-action-link is-danger" type="button" data-remove-item>Hapus item</button>
                                <?php elseif ($ageRangeList): ?>
                                    <span data-age-label-display>Usia <?= esc((string) ($item['label'] ?? '')) ?></span>
                                    <button class="profile-action-link" type="button" data-age-label-toggle aria-expanded="false" aria-controls="<?= esc($ageLabelFieldId . '-editor', 'attr') ?>">
                                        Ubah rentang usia
                                    </button>
                                <?php else: ?>
                                    <span><?= esc($itemLabel) ?> <?= (int) $index + 1 ?></span>
                                    <button class="profile-action-link is-danger" type="button" data-remove-item>Hapus item</button>
                                <?php endif; ?>
                            </div>
                            <?php if ($ageRangeList): ?>
                                <div class="profile-field profile-age-range-editor" id="<?= esc($ageLabelFieldId . '-editor', 'attr') ?>" data-age-label-editor hidden>
                                    <label for="<?= esc($ageLabelFieldId, 'attr') ?>">Rentang usia</label>
                                    <input
                                        class="form-control"
                                        id="<?= esc($ageLabelFieldId, 'attr') ?>"
                                        name="<?= esc($ageLabelFieldName, 'attr') ?>"
                                        type="text"
                                        value="<?= esc((string) ($item['label'] ?? ''), 'attr') ?>"
                                        maxlength="3000"
                                        data-age-label-input
                                    >
                                </div>
                            <?php endif; ?>
                            <?php if ($territoryListType !== ''): ?>
                                <div class="profile-territory-item-editor" data-territory-item-editor hidden>
                                    <?= view('admin/profil/_content-fields', [
                                        'fields' => is_array($item) ? $item : [],
                                        'templates' => is_array($rowTemplate) ? $rowTemplate : [],
                                        'prefix' => $fieldName . '[' . $index . ']',
                                        'readOnlyFields' => $readOnlyFields,
                                        'hiddenFields' => $hiddenFields,
                                    ]) ?>
                                </div>
                            <?php else: ?>
                            <?= view('admin/profil/_content-fields', [
                                'fields' => $ageRangeList ? array_diff_key(is_array($item) ? $item : [], ['label' => true]) : (is_array($item) ? $item : []),
                                'templates' => $ageRangeList ? array_diff_key(is_array($rowTemplate) ? $rowTemplate : [], ['label' => true]) : (is_array($rowTemplate) ? $rowTemplate : []),
                                'prefix' => $fieldName . '[' . $index . ']',
                                'readOnlyFields' => $readOnlyFields,
                                'hiddenFields' => $hiddenFields,
                            ]) ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($territoryListType !== ''): ?>
                    <?php
                    $newItemLabels = [
                        'boundaries' => 'Batas wilayah baru',
                        'sectors' => 'Sektor baru',
                        'orbitations' => 'Lokasi baru',
                        'seasons' => 'Musim baru',
                    ];
                    ?>
                    <template data-list-template>
                        <div class="profile-content-item" data-content-item>
                            <div class="profile-content-item-heading">
                                <span data-territory-item-summary><?= esc($newItemLabels[$territoryListType]) ?></span>
                                <button class="profile-action-link" type="button" data-territory-item-toggle aria-expanded="true">
                                    Selesai mengubah
                                </button>
                                <button class="profile-action-link is-danger" type="button" data-remove-item>Hapus item</button>
                            </div>
                            <div class="profile-territory-item-editor" data-territory-item-editor>
                                <?= view('admin/profil/_content-fields', [
                                    'fields' => is_array($rowTemplate) ? $rowTemplate : [],
                                    'templates' => is_array($rowTemplate) ? $rowTemplate : [],
                                    'prefix' => $fieldName . '[__INDEX__]',
                                    'readOnlyFields' => $readOnlyFields,
                                    'hiddenFields' => $hiddenFields,
                                ]) ?>
                            </div>
                        </div>
                    </template>
                <?php elseif ($ageRangeList): ?>
                    <template data-list-template>
                        <div class="profile-content-item" data-content-item>
                            <div class="profile-content-item-heading">
                                <span data-age-label-display>Rentang usia baru</span>
                            </div>
                            <div class="profile-field profile-age-range-editor">
                                <label for="profile-census-age_rows-__INDEX__-label">Rentang usia</label>
                                <input
                                    class="form-control"
                                    id="profile-census-age_rows-__INDEX__-label"
                                    name="census[age_rows][__INDEX__][label]"
                                    type="text"
                                    value=""
                                    maxlength="3000"
                                    required
                                    data-age-label-input
                                >
                            </div>
                            <?= view('admin/profil/_content-fields', [
                                'fields' => array_diff_key(is_array($rowTemplate) ? $rowTemplate : [], ['label' => true]),
                                'templates' => array_diff_key(is_array($rowTemplate) ? $rowTemplate : [], ['label' => true]),
                                'prefix' => $fieldName . '[__INDEX__]',
                                'readOnlyFields' => $readOnlyFields,
                                'hiddenFields' => $hiddenFields,
                            ]) ?>
                        </div>
                    </template>
                <?php else: ?>
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
                <?php endif; ?>
                <?php if ($ageRangeList): ?>
                    <button class="button button-secondary profile-content-add" type="button" data-add-item>
                        Tambah rentang usia
                    </button>
                <?php else: ?>
                    <button class="button button-secondary profile-content-add" type="button" data-add-item>
                        Tambah <?= esc($addLabel) ?>
                    </button>
                <?php endif; ?>
            </fieldset>
        <?php else: ?>
            <fieldset class="profile-content-object">
                <legend><?= esc((string) ($objectLabels[$key] ?? $label)) ?></legend>
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
        <div class="profile-field <?= in_array($key, ['description', 'administration_text', 'topography_text', 'community_text', 'members_empty'], true) ? 'profile-field-wide' : '' ?>">
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
                        <?= $ariaDescribedBy ?>
                        <?= $value ? 'checked' : '' ?>
                    >
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
            <?php elseif (in_array($key, ['description', 'administration_text', 'topography_text', 'community_text', 'productive_age_note', 'members_empty', 'meta_description'], true) || mb_strlen((string) $value) > 110): ?>
                <textarea
                    class="form-control"
                    id="<?= esc($fieldId, 'attr') ?>"
                    name="<?= esc($fieldName, 'attr') ?>"
                    rows="3"
                    maxlength="3000"
                    <?= $ariaDescribedBy ?>
                ><?= esc((string) $value) ?></textarea>
            <?php else: ?>
                <input
                    class="form-control"
                    id="<?= esc($fieldId, 'attr') ?>"
                    name="<?= esc($fieldName, 'attr') ?>"
                    value="<?= esc((string) $value, 'attr') ?>"
                    <?= $ariaDescribedBy ?>
                    <?= $readOnly ? 'readonly' : '' ?>
                    maxlength="3000"
                >
            <?php endif; ?>
            <?php if ($fieldHelpText !== ''): ?>
                <small class="profile-field-readonly-note profile-field-help" id="<?= esc($fieldHelpId, 'attr') ?>"><?= esc($fieldHelpText) ?></small>
            <?php endif; ?>
            <?php if ($prefix === 'territory' && in_array($key, ['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true)): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldHintId, 'attr') ?>">
                    <?php if ($key === 'boundary_prefix'): ?>Ditampilkan sebelum arah mata angin, misalnya “Sebelah Utara”.
                    <?php elseif ($key === 'sector_prefix'): ?>Ditampilkan sebelum nama sektor, misalnya “Sektor Koinonia”.
                    <?php elseif ($key === 'unit_suffix'): ?>Ditampilkan setelah jumlah unit, misalnya “3 unit”.
                    <?php else: ?>Ditampilkan setelah persentase luas, misalnya “64% dari total wilayah”.
                    <?php endif; ?>
                </small>
            <?php endif; ?>
            <?php if ($prefix === 'census' && in_array($key, ['household_suffix', 'population_suffix'], true)): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldHintId, 'attr') ?>">
                    Contoh isi: “<?= $key === 'household_suffix' ? 'KK dan' : 'jiwa.' ?>”; hasilnya terlihat pada pratinjau di atas.
                </small>
            <?php elseif ($prefix === 'census' && in_array($key, ['legend_male', 'legend_female'], true)): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldHintId, 'attr') ?>">Nama ini tampil sebagai keterangan warna pada grafik.</small>
            <?php elseif ($prefix === 'census' && $key === 'count_separator'): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldHintId, 'attr') ?>">Contoh: Laki-laki: 12. Tanda “:” menjadi pemisah; ganti jika ingin memakai tanda lain.</small>
            <?php elseif ($prefix === 'census' && $key === 'age_unit'): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldHintId, 'attr') ?>">Contoh: “th” untuk menyingkat kata “tahun”.</small>
            <?php endif; ?>
            <?php if ($readOnly): ?>
                <small class="profile-field-readonly-note" id="<?= esc($fieldId . '-note', 'attr') ?>">Dihitung dari modul jumlah KK dan jiwa.</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
