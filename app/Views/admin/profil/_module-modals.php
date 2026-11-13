<?php
helper('modal');
$editPeriod = is_array($editPeriod ?? null) ? $editPeriod : null;
$editMember = is_array($editMember ?? null) ? $editMember : null;
$profile = is_array($profile ?? null) ? $profile : [];
$periods = is_array($periods ?? null) ? $periods : [];
$members = is_array($members ?? null) ? $members : [];
$selectedPeriod = (int) ($selectedPeriod ?? 0);
$territoryGroups = [
    [
        'section' => 'territory',
        'title' => 'Informasi umum wilayah',
        'description' => 'Isi judul dan keterangan status administrasi, topografi, serta kehidupan masyarakat.',
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
        'description' => 'Cantumkan arah dan nama wilayah yang berbatasan, lalu pilih ikon panah yang sesuai.',
        'fields' => array_fill_keys(['boundaries_title', 'boundary_prefix', 'boundaries'], true),
    ],
    [
        'section' => 'territory',
        'title' => 'Sektor pelayanan',
        'description' => 'Masukkan nama sektor, jumlah unit, luas, dan keterangannya.',
        'fields' => array_fill_keys(['sectors', 'sector_prefix', 'unit_suffix', 'area_share_suffix'], true),
    ],
    [
        'section' => 'territory',
        'title' => 'Jarak ke layanan penting',
        'description' => 'Masukkan nama tujuan dan jaraknya berdasarkan informasi yang sudah diperiksa.',
        'fields' => array_fill_keys(['orbit_title', 'orbitations'], true),
    ],
    [
        'section' => 'seasons',
        'title' => 'Musim',
        'description' => 'Atur rentang waktu, nama, dan keterangan untuk setiap musim.',
        'fields' => ['items' => true],
    ],
];
?>
<?php modal_open('profile-territory-modal', false, ['aria-labelledby' => 'profile-territory-title', 'aria-describedby' => 'profile-territory-description'], ['aria-labelledby' => 'profile-territory-title', 'aria-describedby' => 'profile-territory-description']) ?>
<?php modal_content_open() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/modules/territory') ?>" data-profile-ajax-form data-profile-module-form>
    <?= csrf_field() ?>
    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Gambaran Umum Wilayah', 'fw-semibold', ['id' => 'profile-territory-title'], ['id' => 'profile-territory-title']) ?>
        <?php modal_description('Perbarui status administrasi, topografi, batas wilayah, dan informasi musim.', 'rm-description', ['id' => 'profile-territory-description'], ['id' => 'profile-territory-description']) ?>
    </div>
    <?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>
    <?php modal_body_open() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-module-form-grid">
        <?php foreach ($territoryGroups as $index => $group): ?>
            <?php
            $sectionKey = $group['section'];
            $groupFields = array_intersect_key($pageContent[$sectionKey], $group['fields']);
            $groupTemplates = array_intersect_key($pageDefaults[$sectionKey], $group['fields']);
            ?>
            <details class="profile-content-accordion" <?= $index === 0 ? 'open' : '' ?>>
                <summary><?= esc($group['title']) ?></summary>
                <div class="profile-content-accordion-body">
                    <p><?= esc($group['description']) ?></p>
                    <?= view('admin/profil/_content-fields', [
                        'fields' => $groupFields,
                        'templates' => $groupTemplates,
                        'prefix' => $sectionKey,
                        'hiddenFields' => $sectionKey === 'territory' ? ['section_badge'] : [],
                    ]) ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
    <?php modal_section_end() ?>
    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan wilayah</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>

<?php modal_open('profile-census-modal', false, ['aria-labelledby' => 'profile-census-title', 'aria-describedby' => 'profile-census-description'], ['aria-labelledby' => 'profile-census-title', 'aria-describedby' => 'profile-census-description']) ?>
<?php modal_content_open() ?>
<form class="rm-form profile-module-form profile-census-form" method="post" action="<?= site_url('admin/profil/modules/census') ?>" data-profile-ajax-form data-profile-module-form data-profile-census-form>
    <?= csrf_field() ?>
    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Jumlah Kepala Keluarga dan Jiwa', 'fw-semibold', ['id' => 'profile-census-title'], ['id' => 'profile-census-title']) ?>
        <?php modal_description('Masukkan jumlah KK dan rekapitulasi jemaat. Total jiwa dihitung dari jumlah laki-laki dan perempuan.', 'rm-description', ['id' => 'profile-census-description'], ['id' => 'profile-census-description']) ?>
    </div>
    <?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>
    <?php modal_body_open() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-census-calculation" aria-live="polite">
        <span>Total jiwa terhitung</span>
        <strong data-profile-census-total><?= number_format((int) ($totalJiwa ?? 0)) ?></strong>
        <span>jiwa</span>
    </div>
    <nav class="profile-census-step-nav" data-census-step-nav aria-label="Navigasi bagian formulir" hidden>
        <ol class="profile-census-step-list">
            <li>
                <button class="profile-census-step-link" id="census-step-main-nav" type="button" data-census-step-target="census-step-main" aria-controls="census-step-main" aria-current="step">
                    <span class="profile-census-step-number" aria-hidden="true">1</span>
                    <span>Data utama</span>
                </button>
            </li>
            <li>
                <button class="profile-census-step-link" id="census-step-ages-nav" type="button" data-census-step-target="census-step-ages" aria-controls="census-step-ages">
                    <span class="profile-census-step-number" aria-hidden="true">2</span>
                    <span>Kelompok usia</span>
                </button>
            </li>
            <li>
                <button class="profile-census-step-link" id="census-step-labels-nav" type="button" data-census-step-target="census-step-labels" aria-controls="census-step-labels">
                    <span class="profile-census-step-number" aria-hidden="true">3</span>
                    <span>Label &amp; aksesibilitas</span>
                </button>
            </li>
        </ol>
    </nav>
    <div class="profile-module-form-grid">
        <section class="profile-census-step-panel" id="census-step-main" data-census-step-panel aria-labelledby="census-step-main-nav">
            <div class="profile-census-step-panel-body">
                <?= view('admin/profil/_content-fields', [
                    'fields' => array_intersect_key($pageContent['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                        'household_suffix',
                        'population_suffix',
                    ])),
                    'templates' => array_intersect_key($pageDefaults['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                        'household_suffix',
                        'population_suffix',
                    ])),
                    'prefix' => 'census',
                    'hiddenFields' => ['section_badge'],
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
                <?= view('admin/profil/_content-fields', [
                    'fields' => array_diff_key($pageContent['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                        'household_suffix',
                        'population_suffix',
                        'age_rows',
                    ])),
                    'templates' => array_diff_key($pageDefaults['census'], array_flip([
                        'section_badge',
                        'title',
                        'description',
                        'household_count',
                        'household_suffix',
                        'population_suffix',
                        'age_rows',
                    ])),
                    'prefix' => 'census',
                ]) ?>
            </div>
        </section>
    </div>
    <div class="profile-census-step-controls" data-census-step-controls hidden>
        <button class="profile-census-step-previous" type="button" data-census-step-previous disabled>Sebelumnya</button>
        <span data-census-step-count aria-live="polite">Bagian 1 dari 3</span>
        <button class="profile-census-step-next" type="button" data-census-step-next>Berikutnya</button>
    </div>
    <?php modal_section_end() ?>
    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan data jemaat</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>

<?php modal_open('profile-map-modal', false, ['aria-labelledby' => 'profile-map-title', 'aria-describedby' => 'profile-map-description'], ['aria-labelledby' => 'profile-map-title', 'aria-describedby' => 'profile-map-description']) ?>
<?php modal_content_open() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/modules/service-map') ?>" enctype="multipart/form-data" data-profile-ajax-form data-profile-module-form data-profile-map-form>
    <?= csrf_field() ?>
    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Peta Pelayanan', 'fw-semibold', ['id' => 'profile-map-title'], ['id' => 'profile-map-title']) ?>
        <?php modal_description('Pratinjau gambar saat ini. Pilih gambar baru jika ingin menggantinya.', 'rm-description', ['id' => 'profile-map-description'], ['id' => 'profile-map-description']) ?>
    </div>
    <?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>
    <?php modal_body_open() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <figure class="profile-map-preview">
        <img src="<?= esc(base_url($serviceMapPath), 'attr') ?>" alt="<?= esc($serviceMapAlt, 'attr') ?>" data-profile-map-modal-image>
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
    <?php modal_section_end() ?>
    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan peta</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>

<?php modal_open('profile-content-modal', false, ['aria-labelledby' => 'profile-content-title', 'aria-describedby' => 'profile-content-description'], ['aria-labelledby' => 'profile-content-title', 'aria-describedby' => 'profile-content-description']) ?>
<?php modal_content_open() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/save-page-content') ?>" data-profile-ajax-form data-profile-reload>
    <?= csrf_field() ?>
    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Konten Profil Publik', 'fw-semibold', ['id' => 'profile-content-title'], ['id' => 'profile-content-title']) ?>
        <?php modal_description('Edit semua teks halaman, termasuk judul, paragraf, label, daftar, dan pengaturan publikasi Majelis.', 'rm-description', ['id' => 'profile-content-description'], ['id' => 'profile-content-description']) ?>
    </div>
    <?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>
    <?php modal_body_open() ?>
    <div class="profile-content-notice" role="note">Jumlah KK dan jiwa dikelola di kartu sensus. Nilai pada ringkasan Profil dihitung dari data sensus.</div>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <?php foreach ($legacyContent as $sectionKey => $sectionFields): ?>
        <details class="profile-content-section" id="profile-content-<?= esc($sectionKey, 'attr') ?>" <?= $sectionKey === 'hero' ? 'open' : '' ?>>
            <summary>
                <span><?= esc(\App\Libraries\PublicProfileContent::sectionLabels()[$sectionKey] ?? ucfirst($sectionKey)) ?></span>
                <span class="profile-content-summary-note">Edit semua teks dan pengaturan bagian</span>
            </summary>
            <div class="profile-content-section-body">
                <?= view('admin/profil/_content-fields', [
                    'fields' => $sectionFields,
                    'templates' => $pageDefaults[$sectionKey] ?? [],
                    'prefix' => 'content[' . $sectionKey . ']',
                    'readOnlyFields' => $readOnlyFields,
                ]) ?>
            </div>
        </details>
    <?php endforeach; ?>
    <?php modal_section_end() ?>
    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan konten Profil</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>

<?php modal_open('profile-data-modal', false, ['aria-labelledby' => 'profile-data-title', 'aria-describedby' => 'profile-data-description'], ['aria-labelledby' => 'profile-data-title', 'aria-describedby' => 'profile-data-description']) ?>
<?php modal_content_open() ?>
<form class="rm-form profile-module-form" method="post" action="<?= site_url('admin/profil/save-profile') ?>" data-profile-ajax-form data-profile-reload>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= esc($profile['id'] ?? 0) ?>">
    <?php modal_header_open() ?>
    <div class="rm-heading-copy">
        <?php modal_title('Data Jemaat', 'fw-semibold', ['id' => 'profile-data-title'], ['id' => 'profile-data-title']) ?>
        <?php modal_description('Kelola sejarah, visi-misi, alamat, dan kontak yang digunakan oleh halaman Profil.', 'rm-description', ['id' => 'profile-data-description'], ['id' => 'profile-data-description']) ?>
    </div>
    <?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
    <?php modal_section_end() ?>
    <?php modal_body_open() ?>
    <div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
    <div class="profile-form-grid">
        <div class="profile-field profile-field-wide">
            <label for="history">Sejarah</label>
            <textarea class="form-control" id="history" name="history" required><?= esc(old('history') ?? ($profile['history'] ?? '')) ?></textarea>
        </div>
        <div class="profile-field profile-field-wide">
            <label for="vision_mission">Visi dan misi</label>
            <textarea class="form-control" id="vision_mission" name="vision_mission" required><?= esc(old('vision_mission') ?? ($profile['vision_mission'] ?? '')) ?></textarea>
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
            <label for="contact_info">Kontak resmi</label>
            <input class="form-control" id="contact_info" name="contact_info" value="<?= esc(old('contact_info') ?? ($profile['contact_info'] ?? ''), 'attr') ?>" required>
        </div>
    </div>
    <?php modal_section_end() ?>
    <?php modal_footer_open() ?>
    <?php modal_close('Batal', 'btn btn-light') ?>
    <button class="btn btn-primary" type="submit" data-profile-submit>Simpan data jemaat</button>
    <?php modal_section_end() ?>
</form>
<?php modal_section_end() ?>
<?php modal_end() ?>

<?php modal_open('profile-leadership-modal', $editPeriod !== null || $editMember !== null, ['aria-labelledby' => 'profile-leadership-title', 'aria-describedby' => 'profile-leadership-description'], ['aria-labelledby' => 'profile-leadership-title', 'aria-describedby' => 'profile-leadership-description']) ?>
<?php modal_content_open() ?>
<?php modal_header_open() ?>
<div class="rm-heading-copy">
    <?php modal_title('Pengelolaan Majelis', 'fw-semibold', ['id' => 'profile-leadership-title'], ['id' => 'profile-leadership-title']) ?>
    <?php modal_description('Perbarui periode kepengurusan dan data kartu Majelis secara terpisah.', 'rm-description', ['id' => 'profile-leadership-description'], ['id' => 'profile-leadership-description']) ?>
</div>
<?php modal_close('Tutup', 'rm-back-button btn btn-link') ?>
<?php modal_section_end() ?>
<?php modal_body_open() ?>
<div class="profile-form-message" data-profile-form-message role="alert" hidden></div>
<details id="periode-panel" class="profile-content-section" open>
    <summary><span>Periode Majelis</span></summary>
    <div class="profile-content-section-body">
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
</details>

<details id="anggota-panel" class="profile-content-section">
    <summary><span>Kartu Majelis</span></summary>
    <div class="profile-content-section-body">
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
</details>
<?php modal_section_end() ?>
<?php modal_footer_open() ?>
<?php modal_close('Tutup', 'btn btn-light') ?>
<?php modal_section_end() ?>
<?php modal_section_end() ?>
<?php modal_end() ?>
