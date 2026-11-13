<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$pageDefaults = \App\Libraries\PublicProfileContent::defaults();
$pageContent = is_array($pageContent ?? null)
    ? \App\Libraries\PublicProfileContent::mergeForDisplay($pageDefaults, $pageContent)
    : $pageDefaults;
$legacyContent = $pageContent;
unset($legacyContent['territory'], $legacyContent['seasons'], $legacyContent['census'], $legacyContent['worship']);
$largestAgeGroups = is_array($largestAgeGroups ?? null) ? $largestAgeGroups : [];
$serviceMapPath = (string) ($serviceMapPath ?? 'assets/images/gereja/Peta Pelayanan.png');
$serviceMapTitle = (string) ($serviceMapTitle ?? 'Peta Pelayanan');
$serviceMapAlt = (string) ($serviceMapAlt ?? 'Peta pelayanan Jemaat Leahari');
$profile = is_array($profile ?? null) ? $profile : [];
$periods = is_array($periods ?? null) ? $periods : [];
$members = is_array($members ?? null) ? $members : [];
$territorySummary = (string) ($territorySummary ?? 'Informasi wilayah dan musim');
$territoryStatus = (string) ($territoryStatus ?? 'Belum diperbarui');
$censusStatus = (string) ($censusStatus ?? 'Konten awal');
$householdCount = (int) ($householdCount ?? 0);
$totalJiwa = (int) ($totalJiwa ?? 0);
$scheduleCount = (int) ($scheduleCount ?? 0);
$selectedPeriod = (int) ($selectedPeriod ?? 0);
$readOnlyFields = [
    'content[stats][0][value]',
    'content[stats][1][value]',
];
?>
<!-- impeccable-variants-start 827b792b -->
<div data-impeccable-variants="827b792b" data-impeccable-variant-count="2" style="display: contents">
  <!-- Original -->
  <div data-impeccable-variant="original">
    <section class="profile-admin" aria-labelledby="profile-page-title">
        <header class="profile-page-heading">
            <div>
                <h1 id="profile-page-title">Profil Jemaat</h1>
                <p>Kelola informasi wilayah, data jemaat, peta, dan konten halaman publik dari satu tempat.</p>
            </div>

        </header>

        <?php if (! empty($success)): ?>
            <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
        <?php endif; ?>
        <?php if (! empty($error)): ?>
            <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <div class="profile-live-region" data-profile-live role="status" aria-live="polite" aria-atomic="true"></div>

        <div class="profile-module-grid">
            <article class="profile-module-card profile-module-territory" aria-labelledby="territory-module-title">
                <div class="profile-module-heading">
                    <h2 id="territory-module-title">Gambaran Umum Wilayah</h2>
                    <span class="profile-module-status" data-profile-value="territoryStatus"><?= esc($territoryStatus) ?></span>
                </div>
                <p>Status administrasi, topografi, batas wilayah, dan musim pelayanan.</p>
                <div class="profile-module-detail">
                    <span data-profile-value="territorySummary"><?= esc($territorySummary) ?></span>
                </div>
                <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/territory') ?>">Kelola wilayah</a>
            </article>

            <article class="profile-module-card profile-module-census" aria-labelledby="census-module-title">
                <div class="profile-module-heading">
                    <h2 id="census-module-title">Jumlah Kepala Keluarga dan Jiwa</h2>
                    <span class="profile-module-status" data-profile-value="censusStatus"><?= esc($censusStatus) ?></span>
                </div>
                <p>Rekapitulasi jemaat berdasarkan kelompok usia.</p>
                <div class="profile-census-summary" aria-label="Ringkasan data tersimpan">
                    <div><strong data-profile-value="householdCount"><?= number_format($householdCount) ?></strong><span>KK</span></div>
                    <div><strong data-profile-value="totalJiwa"><?= number_format($totalJiwa) ?></strong><span>jiwa</span></div>
                </div>
                <p class="profile-module-note">
                    <?php if ($largestAgeGroups !== []): ?>
                        Kelompok terbesar:
                        <strong data-profile-value="largestAgeGroups"><?= esc(implode(', ', $largestAgeGroups)) ?></strong>
                    <?php else: ?>
                        Belum ada kelompok usia yang terisi.
                    <?php endif; ?>
                </p>
                <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/census') ?>">Kelola data jemaat</a>
            </article>

            <article class="profile-module-card profile-module-worship" aria-labelledby="worship-module-title">
                <div class="profile-module-heading">
                    <h2 id="worship-module-title">Pelayanan Peribadahan</h2>
                    <span class="profile-module-count"><?= number_format(count($pageContent['worship']['items'])) ?> jenis pelayanan</span>
                </div>
                <p>Kelola jenis pelayanan, hari, tempat, dan keterangannya yang tampil di halaman Profil publik.</p>
                <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/worship') ?>">Kelola pelayanan</a>
            </article>

            <article class="profile-module-card profile-module-map" aria-labelledby="map-module-title">
                <div class="profile-map-thumb">
                    <img
                        src="<?= esc(base_url($serviceMapPath), 'attr') ?>"
                        alt="<?= esc($serviceMapAlt, 'attr') ?>"
                        data-profile-map-image
                        loading="lazy"
                        decoding="async">
                </div>
                <div class="profile-map-copy">
                    <div class="profile-module-heading">
                        <h2 id="map-module-title">Peta Pelayanan</h2>
                        <span class="profile-module-status" data-profile-value="serviceMapStatus"><?= ! empty($serviceMapSaved) ? 'Tersimpan' : 'Peta awal' ?></span>
                    </div>
                    <p>Ganti gambar peta atau perbarui keterangan aksesibilitasnya.</p>
                    <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/map') ?>">Kelola peta</a>
                </div>
            </article>

            <article class="profile-module-card profile-module-legacy" aria-labelledby="legacy-module-title">
                <div>
                    <div class="profile-module-heading">
                        <h2 id="legacy-module-title">Konten Profil &amp; Majelis</h2>
                    </div>
                    <p>Edit judul dan teks halaman publik, data jemaat, serta susunan dan periode Majelis. Profil Akun Admin dikelola terpisah.</p>
                </div>
                <div class="profile-module-actions">
                    <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/content') ?>">Edit konten Profil</a>
                    <a class="button button-secondary" href="<?= site_url('admin/profil/kelola/data') ?>">Edit data jemaat</a>
                    <a class="button button-primary" href="<?= site_url('admin/profil/kelola/leadership') ?>">Kelola kartu Majelis</a>
                </div>
            </article>
        </div>
    </section>
  </div>
  <!-- Variants: insert below this line -->
</div>
<!-- impeccable-variants-end 827b792b -->

<?= $this->endSection() ?>