<?php
helper('modal');
$editorModule = (string) ($editorModule ?? '');
$editorOpen = static function (string $id): void {
    echo '<div class="profile-editor-surface" id="' . esc($id, 'attr') . '" data-profile-page>'
        . '<div class="profile-editor-frame"><div class="profile-editor-panel">';
};
$editorContentOpen = static function (): void {
    echo '<div class="profile-editor-content">';
};
$editorHeaderOpen = static function (): void {
    echo '<div class="profile-editor-header">';
};
$editorBodyOpen = static function (): void {
    echo '<div class="profile-modal-body">';
};
$editorFooterOpen = static function (): void {
    echo '<div class="profile-editor-footer">';
};
$editorTitle = static function (string $text, string $class = '', array $desktopProps = [], array $mobileProps = []): void {
    echo '<h2 class="' . esc($class, 'attr') . '"' . modal_attrs($desktopProps + $mobileProps) . '>' . esc($text) . '</h2>';
};
$editorDescription = static function (string $text, string $class = '', array $desktopProps = [], array $mobileProps = []): void {
    echo '<p class="' . esc($class, 'attr') . '"' . modal_attrs($desktopProps + $mobileProps) . '>' . esc($text) . '</p>';
};
$editorClose = static function (): void {
};
$editorSectionEnd = static function (): void {
    echo '</div>';
};
$editorEnd = static function (): void {
    echo '</div></div></div>';
};
$editPeriod = is_array($editPeriod ?? null) ? $editPeriod : null;
$editMember = is_array($editMember ?? null) ? $editMember : null;
$profile = is_array($profile ?? null) ? $profile : [];
$periods = is_array($periods ?? null) ? $periods : [];
$members = is_array($members ?? null) ? $members : [];
$selectedPeriod = (int) ($selectedPeriod ?? 0);
$selectedPeriodName = '';
foreach ($periods as $period) {
    if ((int) ($period['id'] ?? 0) === $selectedPeriod) {
        $selectedPeriodName = (string) ($period['period_name'] ?? '');
        break;
    }
}
$territoryGroups = [
    [
        'section' => 'territory',
        'title' => 'Informasi wilayah',
        'description' => 'Isi informasi wilayah yang sudah dipastikan benar. Perubahan yang disimpan akan tampil di halaman Profil Jemaat.',
        'fields' => array_fill_keys([
            'section_badge',
            'title',
            'administration_title',
            'administration_text',
            'topography_title',
            'topography_text',
            'community_text',
        ], true),
    ],
    [
        'section' => 'territory',
        'title' => 'Batas wilayah',
        'description' => 'Pilih arah mata angin dan isi nama wilayah yang berbatasan.',
        'fields' => array_fill_keys(['boundaries_title', 'boundaries'], true),
    ],
    [
        'section' => 'territory',
        'title' => 'Sektor pelayanan',
        'description' => 'Isi nama setiap sektor, jumlah unit, dan luasnya.',
        'fields' => array_fill_keys(['sectors'], true),
    ],
    [
        'section' => 'territory',
        'title' => 'Jarak ke lokasi penting',
        'description' => 'Isi nama lokasi penting dan jaraknya. Tambahkan atau hapus lokasi sesuai kebutuhan.',
        'fields' => array_fill_keys(['orbit_title', 'orbitations'], true),
    ],
    [
        'section' => 'seasons',
        'title' => 'Musim wilayah',
        'description' => 'Isi periode, nama musim, dan keterangannya.',
        'fields' => ['items' => true],
    ],
    [
        'section' => 'territory',
        'title' => 'Tampilan publik',
        'description' => 'Atur kata dan satuan yang menyertai data di halaman Profil. Data batas, sektor, dan jarak tidak berubah.',
        'fields' => array_fill_keys(['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true),
    ],
];
?>
<?php if ($editorModule === 'territory'): ?>
<?php $editorOpen('profile-territory-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/modules/territory') ?>" data-profile-ajax-form data-profile-module-form data-profile-step-form>
    <?= csrf_field() ?>
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Gambaran Umum Wilayah', 'fw-semibold', ['id' => 'profile-territory-title']) ?>
        <?php $editorDescription('Pilih bagian yang ingin diubah. Pastikan informasi sudah diperiksa; perubahan yang disimpan akan tampil di halaman Profil Jemaat.', 'rm-description', ['id' => 'profile-territory-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <nav class="profile-census-step-nav profile-step-nav" data-profile-step-nav aria-label="Langkah pengisian" hidden>
        <ol class="profile-census-step-list profile-step-list" style="--profile-step-count: <?= count($territoryGroups) ?>">
            <?php foreach ($territoryGroups as $index => $group): ?>
                <?php $stepId = 'profile-territory-step-' . $index; ?>
                <li>
                    <button class="profile-census-step-link profile-step-link" id="<?= esc($stepId, 'attr') ?>-nav" type="button" data-profile-step-target="<?= esc($stepId, 'attr') ?>" aria-controls="<?= esc($stepId, 'attr') ?>" <?= $index === 0 ? 'aria-current="step"' : '' ?>>
                        <span class="profile-census-step-number profile-step-number" aria-hidden="true"><?= $index + 1 ?></span>
                        <span><?= esc($group['title']) ?></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <div class="profile-module-form-grid">
        <?php foreach ($territoryGroups as $index => $group): ?>
            <?php
            $sectionKey = $group['section'];
            $groupFields = array_intersect_key($pageContent[$sectionKey], $group['fields']);
            $groupTemplates = array_intersect_key($pageDefaults[$sectionKey], $group['fields']);
            if ($sectionKey === 'territory' && $group['title'] === 'Tampilan publik') {
                $firstBoundary = $pageContent['territory']['boundaries'][0] ?? ['direction' => '', 'name' => ''];
                $firstSector = $pageContent['territory']['sectors'][0] ?? ['name' => '', 'units' => 0, 'area' => 0, 'area_unit' => ''];
                $sectorAreaTotal = array_sum(array_map(
                    static fn (array $sector): float => (float) ($sector['area'] ?? 0),
                    $pageContent['territory']['sectors'],
                ));
                $sectorAreaShare = $sectorAreaTotal > 0
                    ? (int) round((float) ($firstSector['area'] ?? 0) / $sectorAreaTotal * 100)
                    : 0;
            }
            ?>
            <section class="profile-step-panel" id="profile-territory-step-<?= $index ?>" data-profile-step-panel aria-labelledby="profile-territory-step-<?= $index ?>-nav">
                <div class="profile-content-accordion-body profile-territory-accordion-body">
                    <h3 class="profile-step-panel-heading"><?= esc($group['title']) ?></h3>
                    <p><?= esc($group['description']) ?></p>
                    <?php if ($sectionKey === 'territory' && $group['title'] === 'Tampilan publik'): ?>
                        <div class="profile-census-calculation profile-census-public-preview profile-territory-public-preview">
                            <span>Contoh batas wilayah</span>
                            <output data-territory-preview="boundary"><?= esc(trim((string) $pageContent['territory']['boundary_prefix'] . ' ' . (string) ($firstBoundary['direction'] ?? '') . ' — ' . (string) ($firstBoundary['name'] ?? ''))) ?></output>
                            <span>Contoh sektor pelayanan</span>
                            <output data-territory-preview="sector"><?= esc(trim((string) $pageContent['territory']['sector_prefix'] . ' ' . (string) ($firstSector['name'] ?? '') . ' · ' . (int) ($firstSector['units'] ?? 0) . ' ' . (string) $pageContent['territory']['unit_suffix'] . ' · ' . number_format((float) ($firstSector['area'] ?? 0), 2) . ' ' . (string) ($firstSector['area_unit'] ?? '') . ' · ' . $sectorAreaShare . (string) $pageContent['territory']['area_share_suffix'])) ?></output>
                        </div>
                    <?php endif; ?>
                    <?= view('admin/profil/_content-fields', [
                        'fields' => $groupFields,
                        'templates' => $groupTemplates,
                        'prefix' => $sectionKey,
                        'hiddenFields' => $sectionKey === 'territory' ? ['section_badge'] : [],
                    ]) ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <div class="profile-census-step-controls profile-step-controls" data-profile-step-controls hidden>
        <button class="profile-census-step-previous profile-step-previous" type="button" data-profile-step-previous disabled>Sebelumnya</button>
        <span data-profile-step-count aria-live="polite">Bagian 1 dari <?= count($territoryGroups) ?></span>
        <button class="profile-census-step-next profile-step-next" type="button" data-profile-step-next>Berikutnya</button>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" formnovalidate data-profile-submit>Simpan wilayah</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'census'): ?>
<?php $editorOpen('profile-census-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form profile-census-form" method="post" action="<?= site_url('admin/profil/modules/census') ?>" data-profile-ajax-form data-profile-module-form data-profile-census-form>
    <?= csrf_field() ?>
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Jumlah Kepala Keluarga dan Jiwa', 'fw-semibold', ['id' => 'profile-census-title']) ?>
        <?php $editorDescription('Masukkan jumlah KK dan rekapitulasi jemaat. Total jiwa dihitung dari jumlah laki-laki dan perempuan.', 'rm-description', ['id' => 'profile-census-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-census-calculation" aria-live="polite">
        <span>Total jiwa terhitung</span>
        <strong data-profile-census-total><?= number_format((int) ($totalJiwa ?? 0)) ?></strong>
        <span>jiwa</span>
    </div>
    <nav class="profile-census-step-nav profile-step-nav" data-census-step-nav aria-label="Navigasi bagian formulir" hidden>
        <ol class="profile-census-step-list profile-step-list" style="--profile-step-count: 3">
            <li>
                <button class="profile-census-step-link profile-step-link" id="census-step-main-nav" type="button" data-census-step-target="census-step-main" aria-controls="census-step-main" aria-current="step">
                    <span class="profile-census-step-number profile-step-number" aria-hidden="true">1</span>
                    <span>Data utama</span>
                </button>
            </li>
            <li>
                <button class="profile-census-step-link profile-step-link" id="census-step-ages-nav" type="button" data-census-step-target="census-step-ages" aria-controls="census-step-ages">
                    <span class="profile-census-step-number profile-step-number" aria-hidden="true">2</span>
                    <span>Kelompok usia</span>
                </button>
            </li>
            <li>
                <button class="profile-census-step-link profile-step-link" id="census-step-labels-nav" type="button" data-census-step-target="census-step-labels" aria-controls="census-step-labels">
                    <span class="profile-census-step-number profile-step-number" aria-hidden="true">3</span>
                    <span>Tampilan publik</span>
                </button>
            </li>
        </ol>
    </nav>
    <div class="profile-module-form-grid">
        <section class="profile-census-step-panel" id="census-step-main" data-census-step-panel aria-labelledby="census-step-main-nav">
            <div class="profile-census-step-panel-body">
                <h3 class="profile-step-panel-heading">Data utama</h3>
                <p>Isi jumlah kepala keluarga. Total jiwa dihitung otomatis dari angka pada tiap kelompok usia.</p>
                <?= view('admin/profil/_content-fields', [
                    'fields' => array_intersect_key($pageContent['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                    ])),
                    'templates' => array_intersect_key($pageDefaults['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                    ])),
                    'prefix' => 'census',
                    'hiddenFields' => ['section_badge', 'title', 'description'],
                ]) ?>
            </div>
        </section>
        <section class="profile-census-step-panel" id="census-step-ages" data-census-step-panel aria-labelledby="census-step-ages-nav" hidden>
            <div class="profile-census-step-panel-body">
                <?= view('admin/profil/_content-fields', [
                    'fields' => ['age_rows' => $pageContent['census']['age_rows']],
                    'templates' => ['age_rows' => $pageDefaults['census']['age_rows']],
                    'prefix' => 'census',
                ]) ?>
            </div>
        </section>
        <section class="profile-census-step-panel" id="census-step-labels" data-census-step-panel aria-labelledby="census-step-labels-nav" hidden>
            <div class="profile-census-step-panel-body">
                <h3 class="profile-step-panel-heading"><?= esc((string) ($pageContent['census']['title'] ?? '')) ?></h3>
                <p><?= esc((string) ($pageContent['census']['description'] ?? '')) ?></p>
                <div class="profile-census-calculation profile-census-public-preview" aria-live="polite">
                    <span>Pratinjau jumlah di halaman Profil</span>
                    <output data-census-public-preview>
                        <?= number_format((int) ($pageContent['census']['household_count'] ?? 0)) ?> <?= esc(trim((string) ($pageContent['census']['household_suffix'] ?? ''))) ?> <?= number_format((int) ($totalJiwa ?? 0)) ?> <?= esc(trim((string) ($pageContent['census']['population_suffix'] ?? ''))) ?>
                    </output>
                </div>
                <?= view('admin/profil/_content-fields', [
                    'fields' => array_diff_key($pageContent['census'], array_flip(['household_count', 'age_rows'])),
                    'templates' => array_diff_key($pageDefaults['census'], array_flip(['household_count', 'age_rows'])),
                    'prefix' => 'census',
                    'hiddenFields' => array_keys(array_diff_key($pageContent['census'], array_flip(['household_count', 'age_rows']))),
                ]) ?>
            </div>
        </section>
    </div>
    <div class="profile-census-step-controls profile-step-controls" data-census-step-controls hidden>
        <button class="profile-census-step-previous profile-step-previous" type="button" data-census-step-previous disabled>Sebelumnya</button>
        <span data-census-step-count aria-live="polite">Bagian 1 dari 3</span>
        <button class="profile-census-step-next profile-step-next" type="button" data-census-step-next>Berikutnya</button>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan data jemaat</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'map'): ?>
<?php $editorOpen('profile-map-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/modules/service-map') ?>" enctype="multipart/form-data" data-profile-ajax-form data-profile-module-form data-profile-map-form>
    <?= csrf_field() ?>
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Peta Pelayanan', 'fw-semibold', ['id' => 'profile-map-title']) ?>
        <?php $editorDescription('Pratinjau gambar saat ini. Pilih gambar baru jika ingin menggantinya.', 'rm-description', ['id' => 'profile-map-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <figure class="profile-map-preview">
        <img src="<?= esc(base_url($serviceMapPath), 'attr') ?>" alt="<?= esc($serviceMapAlt, 'attr') ?>" data-profile-map-preview>
        <figcaption>Gambar publik baru aktif setelah file berhasil diunggah dan disimpan.</figcaption>
    </figure>
    <div class="profile-field profile-field-wide">
        <label for="service-map-title-input">Judul bagian peta</label>
        <input class="form-control" id="service-map-title-input" name="service_map_title" value="<?= esc($serviceMapTitle, 'attr') ?>" maxlength="150" required>
    </div>
    <div class="profile-field profile-field-wide">
        <label for="service-map-file">Gambar peta <span>(opsional, JPG/PNG/WEBP, maksimal 5 MB)</span></label>
        <input class="form-control" id="service-map-file" name="service_map" type="file" accept="image/jpeg,image/png,image/webp">
        <small class="profile-field-error" data-profile-file-error role="alert" hidden></small>
    </div>
    <div class="profile-field profile-field-wide">
        <label for="service-map-alt">Alt text</label>
        <input class="form-control" id="service-map-alt" name="service_map_alt" value="<?= esc($serviceMapAlt, 'attr') ?>" maxlength="255" required>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan peta</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'worship'): ?>
<?php $editorOpen('profile-worship-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/save-page-content') ?>" data-profile-ajax-form data-profile-reload>
    <?= csrf_field() ?>
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Pelayanan Peribadahan', 'fw-semibold', ['id' => 'profile-worship-title']) ?>
        <?php $editorDescription('Perbarui daftar pelayanan yang tampil di halaman Profil publik. Perubahan ini bukan untuk jadwal ibadah bertanggal.', 'rm-description', ['id' => 'profile-worship-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-form-grid">
        <?= view('admin/profil/_content-fields', [
            'fields' => $pageContent['worship'],
            'templates' => $pageDefaults['worship'],
            'prefix' => 'content[worship]',
            'hiddenFields' => ['section_badge'],
        ]) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan pelayanan</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'content'): ?>
<?php
$contentSettingsKeys = [
    'hero' => array_fill_keys([
        'page_title',
        'meta_description',
        'badge',
        'title',
        'links',
        'stats_aria_label',
        'map_link_label',
    ], true),
    'stats' => array_fill_keys(['icon', 'label'], true),
    'leadership' => array_fill_keys([
        'title',
        'members_title',
        'period_prefix',
        'location_title',
        'address_title',
        'contact_title',
    ], true),
];
$contentFieldsBySection = [];
$contentSettingsBySection = [];
foreach ($legacyContent as $sectionKey => $sectionFields) {
    $settingsKeys = $contentSettingsKeys[$sectionKey] ?? [];
    if ($sectionKey === 'stats') {
        $contentFieldsBySection[$sectionKey] = array_map(
            static fn (array $stat): array => array_diff_key($stat, $settingsKeys),
            $sectionFields,
        );
        $contentSettingsBySection[$sectionKey] = array_map(
            static fn (array $stat): array => array_intersect_key($stat, $settingsKeys),
            $sectionFields,
        );
        continue;
    }

    $contentFieldsBySection[$sectionKey] = array_diff_key($sectionFields, $settingsKeys);
    if ($settingsKeys !== []) {
        $contentSettingsBySection[$sectionKey] = array_intersect_key($sectionFields, $settingsKeys);
    }
}
$contentSettingsId = 'profile-content-settings';
?>
<?php $editorOpen('profile-content-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/save-page-content') ?>" data-profile-ajax-form data-profile-reload data-profile-step-form>
    <?= csrf_field() ?>
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Konten Profil Publik', 'fw-semibold', ['id' => 'profile-content-title']) ?>
        <?php $editorDescription('Perbarui isi Profil. Judul dan label halaman dikelola terpisah di bagian Pengaturan judul dan label.', 'rm-description', ['id' => 'profile-content-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-content-notice" role="note">Jumlah KK dan jiwa dikelola di kartu sensus. Nilai pada ringkasan Profil dihitung dari data sensus.</div>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <nav class="profile-census-step-nav profile-step-nav" data-profile-step-nav aria-label="Langkah pengisian" hidden>
        <ol class="profile-census-step-list profile-step-list" style="--profile-step-count: <?= count($contentFieldsBySection) ?>">
            <?php foreach ($contentFieldsBySection as $sectionKey => $sectionFields): ?>
                <?php $stepId = 'profile-content-' . $sectionKey; ?>
                <li>
                    <button class="profile-census-step-link profile-step-link" id="<?= esc($stepId, 'attr') ?>-nav" type="button" data-profile-step-target="<?= esc($stepId, 'attr') ?>" aria-controls="<?= esc($stepId, 'attr') ?>" <?= $sectionKey === array_key_first($legacyContent) ? 'aria-current="step"' : '' ?>>
                        <span class="profile-census-step-number profile-step-number" aria-hidden="true"><?= array_search($sectionKey, array_keys($legacyContent), true) + 1 ?></span>
                        <span><?= esc(\App\Libraries\PublicProfileContent::sectionLabels()[$sectionKey] ?? ucfirst($sectionKey)) ?></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php foreach ($contentFieldsBySection as $sectionKey => $sectionFields): ?>
        <?php
        $sectionTemplates = $pageDefaults[$sectionKey] ?? [];
        $sectionObjectLabels = $sectionKey === 'stats'
            ? array_column($legacyContent[$sectionKey], 'label')
            : [];
        ?>
        <section class="profile-content-section profile-step-panel" id="profile-content-<?= esc($sectionKey, 'attr') ?>" data-profile-step-panel aria-labelledby="profile-content-<?= esc($sectionKey, 'attr') ?>-nav">
            <div class="profile-content-section-body">
                <h3 class="profile-step-panel-heading"><?= esc(\App\Libraries\PublicProfileContent::sectionLabels()[$sectionKey] ?? ucfirst($sectionKey)) ?></h3>
                <p class="profile-step-panel-description">Ubah isi yang ingin ditampilkan pada halaman Profil Jemaat.</p>
                <?= view('admin/profil/_content-fields', [
                    'fields' => $sectionFields,
                    'templates' => $sectionTemplates,
                    'prefix' => 'content[' . $sectionKey . ']',
                    'readOnlyFields' => $readOnlyFields,
                    'objectLabels' => $sectionObjectLabels,
                ]) ?>
            </div>
        </section>
    <?php endforeach; ?>
    <section class="profile-content-section profile-step-panel" id="<?= esc($contentSettingsId, 'attr') ?>" hidden aria-labelledby="<?= esc($contentSettingsId, 'attr') ?>-heading">
        <div class="profile-content-section-body">
            <h3 class="profile-step-panel-heading" id="<?= esc($contentSettingsId, 'attr') ?>-heading">Pengaturan judul dan label</h3>
            <p class="profile-step-panel-description">Atur teks judul, label, dan nama tautan yang tampil di halaman Profil. Isian ini terpisah dari isi utama.</p>
            <?php foreach ($contentSettingsBySection as $sectionKey => $settingsFields): ?>
                <details class="profile-content-settings-group" <?= $sectionKey === array_key_first($contentSettingsBySection) ? 'open' : '' ?>>
                    <summary><?= esc(\App\Libraries\PublicProfileContent::sectionLabels()[$sectionKey] ?? ucfirst($sectionKey)) ?></summary>
                    <?php
                    $settingsHiddenFields = $sectionKey === 'hero'
                        ? ['page_title', 'meta_description', 'badge', 'stats_aria_label', 'map_link_label', 'target']
                        : ($sectionKey === 'stats' ? ['icon'] : []);
                    $heroLinks = $sectionKey === 'hero' ? ($settingsFields['links'] ?? []) : [];
                    if ($sectionKey === 'hero') {
                        unset($settingsFields['links']);
                    }
                    ?>
                    <?= view('admin/profil/_content-fields', [
                        'fields' => $settingsFields,
                        'templates' => $pageDefaults[$sectionKey] ?? [],
                        'prefix' => 'content[' . $sectionKey . ']',
                        'readOnlyFields' => $readOnlyFields,
                        'hiddenFields' => $settingsHiddenFields,
                    ]) ?>
                    <?php if ($sectionKey === 'hero' && $heroLinks !== []): ?>
                        <details class="profile-content-settings-links">
                            <summary>Nama tautan bagian Profil</summary>
                            <?= view('admin/profil/_content-fields', [
                                'fields' => ['links' => $heroLinks],
                                'templates' => ['links' => $pageDefaults['hero']['links'] ?? []],
                                'prefix' => 'content[hero]',
                                'readOnlyFields' => $readOnlyFields,
                                'hiddenFields' => ['target'],
                            ]) ?>
                        </details>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        </div>
    </section>
    <div class="profile-census-step-controls profile-step-controls" data-profile-step-controls hidden>
        <button class="profile-census-step-previous profile-step-previous" type="button" data-profile-step-previous disabled>Sebelumnya</button>
        <span data-profile-step-count aria-live="polite">Bagian 1 dari <?= count($contentFieldsBySection) ?></span>
        <button class="profile-census-step-next profile-step-next" type="button" data-profile-step-next>Berikutnya</button>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" formnovalidate data-profile-submit>Simpan konten Profil</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'data'): ?>
<?php $editorOpen('profile-data-editor') ?>
<?php $editorContentOpen() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/save-profile') ?>" data-profile-ajax-form data-profile-reload>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= esc($profile['id'] ?? 0) ?>">
    <?php $editorHeaderOpen() ?>
    <div class="rm-heading-copy">
        <?php $editorTitle('Data Jemaat', 'fw-semibold', ['id' => 'profile-data-title']) ?>
        <?php $editorDescription('Isi informasi yang sudah tersedia dan disetujui. Kolom opsional yang kosong tidak ditampilkan di halaman publik.', 'rm-description', ['id' => 'profile-data-description']) ?>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorBodyOpen() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-form-grid">
        <div class="profile-field profile-field-wide">
            <label for="history">Sejarah <span>(opsional)</span></label>
            <textarea class="form-control" id="history" name="history"><?= esc(old('history') ?? ($profile['history'] ?? '')) ?></textarea>
        </div>
        <div class="profile-field profile-field-wide">
            <label for="vision_mission">Visi dan misi <span>(opsional)</span></label>
            <textarea class="form-control" id="vision_mission" name="vision_mission"><?= esc(old('vision_mission') ?? ($profile['vision_mission'] ?? '')) ?></textarea>
        </div>
        <div class="profile-field profile-field-wide">
            <label for="address">Alamat</label>
            <textarea class="form-control" id="address" name="address" required><?= esc(old('address') ?? ($profile['address'] ?? '')) ?></textarea>
        </div>
        <div class="profile-field">
            <label for="maps_url">Tautan Google Maps <span>(opsional)</span></label>
            <input class="form-control" id="maps_url" name="maps_url" type="url" value="<?= esc(old('maps_url') ?? ($profile['maps_url'] ?? ''), 'attr') ?>" placeholder="https://maps.google.com/...">
        </div>
        <div class="profile-field">
            <label for="contact_info">Kontak resmi <span>(opsional)</span></label>
            <input class="form-control" id="contact_info" name="contact_info" value="<?= esc(old('contact_info') ?? ($profile['contact_info'] ?? ''), 'attr') ?>">
        </div>
    </div>
    <?php $editorSectionEnd() ?>
    <?php $editorFooterOpen() ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan data jemaat</button>
    <?php $editorSectionEnd() ?>
</form>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>

<?php if ($editorModule === 'leadership'): ?>
<?php $editorOpen('profile-leadership-editor') ?>
<?php $editorContentOpen() ?>
<?php $editorHeaderOpen() ?>
<div class="rm-heading-copy">
    <?php $editorTitle('Pengelolaan Majelis', 'fw-semibold', ['id' => 'profile-leadership-title']) ?>
    <?php $editorDescription('Perbarui periode kepengurusan dan data kartu Majelis secara terpisah.', 'rm-description', ['id' => 'profile-leadership-description']) ?>
</div>
<?php $editorSectionEnd() ?>
<?php $editorBodyOpen() ?>
<div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
<div class="profile-leadership-stepper" data-leadership-stepper>
    <nav class="profile-census-step-nav profile-step-nav" data-leadership-step-nav aria-label="Bagian pengelolaan Majelis" hidden>
        <ol class="profile-step-list" style="--profile-step-count: 3">
            <li>
                <button class="profile-step-link" id="leadership-step-period-nav" type="button" data-leadership-step-target="periode-panel" aria-controls="periode-panel" aria-current="step">
                    <span class="profile-step-number" aria-hidden="true">1</span>
                    <span>Periode Majelis</span>
                </button>
            </li>
            <li>
                <button class="profile-step-link" id="leadership-step-member-nav" type="button" data-leadership-step-target="anggota-form-panel" aria-controls="anggota-form-panel">
                    <span class="profile-step-number" aria-hidden="true">2</span>
                    <span>Data anggota</span>
                </button>
            </li>
            <li>
                <button class="profile-step-link" id="leadership-step-list-nav" type="button" data-leadership-step-target="anggota-panel" aria-controls="anggota-panel">
                    <span class="profile-step-number" aria-hidden="true">3</span>
                    <span>Daftar anggota</span>
                </button>
            </li>
        </ol>
    </nav>
    <section id="periode-panel" class="profile-content-section profile-step-panel" data-leadership-step-panel aria-labelledby="leadership-step-period-nav">
    <div class="profile-content-section-body">
        <h3 class="profile-step-panel-heading">Periode Majelis</h3>
        <form class="profile-management-form" method="post" action="<?= site_url('admin/profil/save-periode') ?>" data-profile-ajax-form data-profile-reload data-profile-confirm="<?= $editPeriod ? 'Simpan perubahan periode?' : '' ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($editPeriod['id'] ?? 0) ?>" data-profile-period-id>
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="period_name">Nama periode</label>
                    <input class="form-control" id="period_name" name="period_name" value="<?= esc($editPeriod['period_name'] ?? '', 'attr') ?>" maxlength="120" required>
                </div>
                <div class="profile-field">
                    <label for="start_date">Tanggal mulai</label>
                    <input class="form-control" id="start_date" name="start_date" type="date" value="<?= esc($editPeriod['start_date'] ?? '', 'attr') ?>" required>
                </div>
                <div class="profile-field">
                    <label for="end_date">Tanggal selesai <span>(opsional)</span></label>
                    <input class="form-control" id="end_date" name="end_date" type="date" value="<?= esc($editPeriod['end_date'] ?? '', 'attr') ?>">
                </div>
                <div class="profile-field">
                    <label for="is_active">Status periode</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="0" <?= empty($editPeriod['is_active']) ? 'selected' : '' ?>>Tidak aktif</option>
                        <option value="1" <?= ! empty($editPeriod['is_active']) ? 'selected' : '' ?>>Jadikan aktif</option>
                    </select>
                </div>
            </div>
            <div class="profile-form-actions">
                <button class="button button-primary" type="submit" data-profile-submit><?= $editPeriod ? 'Simpan perubahan periode' : 'Simpan periode' ?></button>
                <button class="button button-secondary" type="button" data-edit-period-reset>Batal mengubah</button>
            </div>
        </form>

        <div class="profile-table-wrap">
            <table class="profile-table">
                <thead><tr><th>Nama periode</th><th>Rentang</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if ($periods === []): ?>
                    <tr><td class="profile-table-empty" colspan="4">Belum ada data periode Majelis.</td></tr>
                <?php else: ?>
                    <?php foreach ($periods as $period): ?>
                        <tr>
                            <td><?= esc($period['period_name']) ?></td>
                            <td><?= esc($period['start_date'] ?? '-') ?> – <?= esc($period['end_date'] ?? 'sekarang') ?></td>
                            <td><span class="profile-period-status <?= ! empty($period['is_active']) ? 'is-active' : '' ?>"><?= ! empty($period['is_active']) ? 'Aktif' : 'Tidak aktif' ?></span></td>
                            <td class="profile-table-actions">
                                <button
                                    class="profile-action-link"
                                    type="button"
                                    data-edit-period
                                    data-period="<?= esc(json_encode([
                                        'id' => (int) $period['id'],
                                        'period_name' => (string) $period['period_name'],
                                        'start_date' => (string) $period['start_date'],
                                        'end_date' => (string) ($period['end_date'] ?? ''),
                                        'is_active' => (int) $period['is_active'],
                                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), 'attr') ?>"
                                >Edit</button>
                                <?php if (empty($period['is_active'])): ?>
                                    <form method="post" action="<?= site_url('admin/profil/activate-periode/' . (int) $period['id']) ?>" data-profile-ajax-form data-profile-reload>
                                        <?= csrf_field() ?>
                                        <button class="profile-action-link" type="submit">Aktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <span class="profile-current-period">Periode saat ini</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="anggota-form-panel" class="profile-content-section profile-step-panel" data-leadership-step-panel aria-labelledby="leadership-step-member-nav">
    <div class="profile-content-section-body">
        <h3 class="profile-step-panel-heading">Data anggota</h3>
        <div class="profile-period-filter">
            <label for="member-period-id">Periode yang dikelola</label>
            <select class="form-select" id="member-period-id" data-profile-period-select>
                <?php foreach ($periods as $period): ?>
                    <option value="<?= (int) $period['id'] ?>" <?= $selectedPeriod === (int) $period['id'] ? 'selected' : '' ?>>
                        <?= esc($period['period_name']) ?><?= ! empty($period['is_active']) ? ' · Aktif' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selectedPeriod <= 0): ?>
            <p class="profile-inline-note" role="status">Tambahkan atau pilih periode Majelis sebelum memasukkan anggota.</p>
        <?php endif; ?>
        <form id="member-form" class="profile-management-form" method="post" action="<?= site_url('admin/profil/save-anggota') ?>" enctype="multipart/form-data" data-profile-ajax-form data-profile-reload>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($editMember['id'] ?? 0) ?>" data-profile-member-id>
            <input type="hidden" name="periode_id" value="<?= esc((string) ($editMember['periode_id'] ?? $selectedPeriod), 'attr') ?>">
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="member_name">Nama</label>
                    <input class="form-control" id="member_name" name="name" value="<?= esc($editMember['name'] ?? '', 'attr') ?>" maxlength="150" required <?= $selectedPeriod <= 0 ? 'disabled' : '' ?>>
                </div>
                <div class="profile-field">
                    <label for="member_position">Jabatan</label>
                    <input class="form-control" id="member_position" name="position" value="<?= esc($editMember['position'] ?? '', 'attr') ?>" maxlength="150" required <?= $selectedPeriod <= 0 ? 'disabled' : '' ?>>
                </div>
                <div class="profile-field profile-field-wide">
                    <label for="member_photo">Foto <span>(opsional, JPG/PNG/WEBP, maksimal 2 MB)</span></label>
                    <input class="form-control" id="member_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" <?= $selectedPeriod <= 0 ? 'disabled' : '' ?>>
                    <?php if (! empty($editMember['photo_path'])): ?>
                        <img class="profile-member-photo profile-member-photo-preview" src="<?= esc(base_url($editMember['photo_path']), 'attr') ?>" alt="Foto <?= esc($editMember['name'], 'attr') ?>">
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-form-actions">
                <button class="button button-primary<?= $editMember ? ' is-icon-only' : '' ?>" type="submit" data-profile-submit <?= $selectedPeriod <= 0 ? 'disabled' : '' ?>>
                    <svg class="profile-submit-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none">
                        <path d="M5 4h12l3 3v13H4V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M8 4v6h8V4M8 20v-6h8v6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                    <span class="profile-submit-label"><?= $editMember ? 'Simpan perubahan anggota' : 'Simpan anggota' ?></span>
                </button>
                <button class="button button-secondary" type="button" data-edit-member-reset>Batal mengubah</button>
            </div>
        </form>
    </div>
</section>

<section id="anggota-panel" class="profile-content-section profile-step-panel" data-leadership-step-panel aria-labelledby="leadership-step-list-nav">
    <div class="profile-content-section-body">
        <h3 class="profile-step-panel-heading">Daftar anggota</h3>
        <div class="profile-period-filter">
            <span>Periode yang dikelola</span>
            <strong><?= esc($selectedPeriodName !== '' ? $selectedPeriodName : 'Belum ada periode') ?></strong>
        </div>
        <div class="profile-table-wrap">
            <table class="profile-table">
                <thead><tr><th>Nama</th><th>Jabatan</th><th>Foto</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if ($members === []): ?>
                    <tr><td class="profile-table-empty" colspan="4">Belum ada anggota pada periode ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= esc($member['name']) ?></td>
                            <td><?= esc($member['position']) ?></td>
                            <td>
                                <?php if (! empty($member['photo_path'])): ?>
                                    <img class="profile-member-photo" src="<?= esc(base_url($member['photo_path']), 'attr') ?>" alt="Foto <?= esc($member['name'], 'attr') ?>">
                                <?php else: ?>
                                    <span class="profile-no-photo">Belum ada foto</span>
                                <?php endif; ?>
                            </td>
                            <td class="profile-table-actions">
                                <button
                                    class="profile-action-link"
                                    type="button"
                                    data-edit-member
                                    data-member="<?= esc(json_encode([
                                        'id' => (int) $member['id'],
                                        'periode_id' => (int) $member['periode_id'],
                                        'name' => (string) $member['name'],
                                        'position' => (string) $member['position'],
                                        'photo_path' => (string) ($member['photo_path'] ?? ''),
                                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), 'attr') ?>"
                                >Edit</button>
                                <form method="post" action="<?= site_url('admin/profil/delete-anggota/' . (int) $member['id']) ?>" data-profile-ajax-form data-profile-reload data-profile-confirm="Hapus anggota Majelis ini?">
                                    <?= csrf_field() ?>
                                    <button class="profile-action-link is-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
    <div class="profile-step-controls" data-leadership-step-controls hidden>
        <button class="profile-step-previous" type="button" data-leadership-step-previous disabled>Sebelumnya</button>
        <span data-leadership-step-count aria-live="polite">Bagian 1 dari 3</span>
        <button class="profile-step-next" type="button" data-leadership-step-next>Berikutnya</button>
    </div>
</div>
<?php $editorSectionEnd() ?>
<?php $editorFooterOpen() ?>
<?php $editorSectionEnd() ?>
<?php $editorSectionEnd() ?>
<?php $editorEnd() ?>
<?php endif; ?>
