<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<?php
$hero = $content['hero'];
$territory = $content['territory'];
$census = $content['census'];
$leadership = $content['leadership'];
$worship = $content['worship'];
$history = trim((string) ($profile['history'] ?? ''));
$visionMission = trim((string) ($profile['vision_mission'] ?? ''));
$address = trim((string) ($profile['address'] ?? ''));
$mapsUrl = trim((string) ($profile['maps_url'] ?? ''));
$contactInfo = trim((string) ($profile['contact_info'] ?? ''));
$mapsScheme = strtolower((string) parse_url($mapsUrl, PHP_URL_SCHEME));
if (
    filter_var($mapsUrl, FILTER_VALIDATE_URL) === false
    || ! in_array($mapsScheme, ['http', 'https'], true)
) {
    $mapsUrl = '';
}
$leaders = ! empty($showMembers) && ! empty($members)
    ? array_map(static fn (array $member): array => [
        'name' => $member['name'],
        'role' => $member['position'],
        'image' => $member['photo_path'] ?? null,
    ], $members)
    : [];
?>
<div class="profil-page">
    <section class="profil-hero text-white">
        <div class="container py-5 position-relative">
            <h1 class="display-5 fw-bold mb-3"><?= esc($hero['title']) ?></h1>
            <p class="lead col-lg-7 mb-4"><?= esc($hero['description']) ?></p>
        </div>
    </section>

    <section class="container stat-strip" aria-label="<?= esc($hero['stats_aria_label'], 'attr') ?>">
        <div class="row g-3">
            <?php foreach ($content['stats'] as $stat): ?>
                <div class="col-6 col-lg-3">
                    <div class="card card-lift border-0 shadow h-100 rounded-4">
                        <div class="card-body text-center py-4">
                            <i class="bi <?= esc($stat['icon'], 'attr') ?> fs-2 text-primary" aria-hidden="true"></i>
                            <div class="fs-4 fw-bold mt-2"><?= esc($stat['value']) ?></div>
                            <div class="text-body-secondary small"><?= esc($stat['label']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($history !== '' || $visionMission !== ''): ?>
        <section id="sejarah-visi-misi" class="container py-5 profile-story-section" aria-labelledby="profile-story-title">
            <div class="profile-story-panel">
                <h2 id="profile-story-title">Mengenal Jemaat Leahari</h2>
                <div class="profile-story-grid">
                    <?php if ($history !== ''): ?>
                        <article class="profile-story-block">
                            <h3>Sejarah</h3>
                            <div class="profile-story-copy"><?= nl2br(esc($history), false) ?></div>
                        </article>
                    <?php endif; ?>
                    <?php if ($visionMission !== ''): ?>
                        <article class="profile-story-block">
                            <h3>Visi dan Misi</h3>
                            <div class="profile-story-copy"><?= nl2br(esc($visionMission), false) ?></div>
                        </article>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($address !== '' || $mapsUrl !== '' || $contactInfo !== ''): ?>
        <section id="profil-kontak" class="container py-4 profile-contact-section" aria-labelledby="profile-contact-title">
            <h2 class="fw-bold mb-4" id="profile-contact-title">Informasi Jemaat</h2>
            <dl class="row g-3 mb-0">
                <?php if ($address !== ''): ?>
                    <div class="col-md-6">
                        <dt>Alamat</dt>
                        <dd class="mb-0"><address><?= nl2br(esc($address), false) ?></address></dd>
                    </div>
                <?php endif; ?>
                <?php if ($contactInfo !== ''): ?>
                    <div class="col-md-6">
                        <dt>Kontak resmi</dt>
                        <dd class="mb-0"><?= nl2br(esc($contactInfo), false) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($mapsUrl !== ''): ?>
                    <div class="col-12">
                        <dt>Lokasi</dt>
                        <dd class="mb-0">
                            <a href="<?= esc($mapsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">Lihat lokasi di Google Maps</a>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>
    <?php endif; ?>

    <section id="peta-pelayanan" class="container py-5 service-map-section" aria-labelledby="service-map-title">
        <div class="mb-4">
            <h2 class="fw-bold" id="service-map-title"><?= esc($serviceMapTitle) ?></h2>
        </div>
        <img
            class="img-fluid w-100 rounded-4 shadow-sm service-map-image"
            src="<?= esc(base_url($serviceMapPath), 'attr') ?>"
            alt="<?= esc($serviceMapAlt, 'attr') ?>"
            width="817"
            height="551"
            loading="lazy"
            decoding="async">
    </section>

    <div id="majelis-jemaat" class="container-xl pb-4 pb-lg-5">
        <div class="section-heading leadership-heading">
            <h2 id="leadership-title"><?= esc($leadership['members_title']) ?></h2>
        </div>
        <?php if ($leaders !== []): ?>
            <div class="leadership-carousel" data-leadership-carousel role="region" aria-roledescription="carousel" aria-label="Susunan Majelis Jemaat">
                <div class="leadership-grid" data-carousel-track>
                    <?php foreach ($leaders as $index => $leader): ?>
                        <article class="leader-card" data-carousel-slide aria-roledescription="slide" aria-label="<?= esc(($index + 1) . ' dari ' . count($leaders) . ': ' . $leader['name'], 'attr') ?>">
                            <?php if (! empty($leader['image'])): ?>
                                <img class="leader-card-image" src="<?= esc(base_url($leader['image']), 'attr') ?>" alt="" aria-hidden="true">
                            <?php endif; ?>
                            <div class="leader-card-body">
                                <h3><?= esc($leader['name']) ?></h3>
                                <p><?= esc($leader['role']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="leadership-carousel-controls" data-carousel-controls hidden>
                    <button class="leadership-carousel-arrow" type="button" data-carousel-previous aria-label="Anggota sebelumnya">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6M21 12H9" /></svg>
                    </button>
                    <div class="leadership-carousel-middle">
                        <div class="leadership-carousel-indicators" role="group" aria-label="Pilih anggota">
                            <?php foreach ($leaders as $index => $leader): ?>
                                <button class="leadership-carousel-indicator" type="button" data-carousel-indicator="<?= $index ?>" aria-label="<?= esc('Tampilkan anggota ' . ($index + 1) . ': ' . $leader['name'], 'attr') ?>" aria-pressed="false">
                                    <span aria-hidden="true"></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button class="leadership-carousel-arrow" type="button" data-carousel-next aria-label="Anggota berikutnya">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6m6 6H3" /></svg>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <p class="home-empty"><?= esc($leadership['members_empty']) ?></p>
        <?php endif; ?>
    </div>

    <section id="wilayah" class="container py-5">
        <div class="mb-4">
            <span class="badge rounded-pill badge-soft"><?= esc($territory['section_badge']) ?></span>
            <h2 class="fw-bold mt-2"><?= esc($territory['title']) ?></h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold"><?= esc($territory['administration_title']) ?></h3>
                        <p class="text-body-secondary"><?= esc($territory['administration_text']) ?></p>
                        <h3 class="h5 fw-bold mt-4"><?= esc($territory['topography_title']) ?></h3>
                        <p class="text-body-secondary"><?= esc($territory['topography_text']) ?></p>
                        <p class="text-body-secondary mb-0"><?= esc($territory['community_text']) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3"><?= esc($territory['boundaries_title']) ?></h3>
                        <div class="row g-2">
                            <?php foreach ($territory['boundaries'] as $boundary): ?>
                                <div class="col-6">
                                    <div class="batas-item rounded-3 p-3 h-100">
                                        <i class="bi <?= esc($boundary['icon'], 'attr') ?> text-primary fs-4" aria-hidden="true"></i>
                                        <div class="small text-body-secondary mt-1"><?= esc($territory['boundary_prefix']) ?> <?= esc($boundary['direction']) ?></div>
                                        <div class="fw-semibold"><?= esc($boundary['name']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <?php foreach ($territory['sectors'] as $sector): ?>
                <?php
                $areaShare = $areaTotal > 0 ? (int) round($sector['area'] / $areaTotal * 100) : 0;
                ?>
                <div class="col-md-4">
                    <div class="card card-lift border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <h3 class="h5 fw-bold mb-0"><?= esc($territory['sector_prefix']) ?> <?= esc($sector['name']) ?></h3>
                                <span class="badge rounded-pill badge-soft"><?= (int) $sector['units'] ?> <?= esc($territory['unit_suffix']) ?></span>
                            </div>
                            <div class="display-6 fw-bold mt-3"><?= number_format((float) $sector['area'], 2) ?> <small class="fs-6 text-body-secondary"><?= esc($sector['area_unit']) ?></small></div>
                            <div class="progress mt-3" style="height:8px" role="progressbar" aria-valuenow="<?= $areaShare ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: <?= $areaShare ?>%"></div>
                            </div>
                            <div class="small text-body-secondary mt-2"><?= $areaShare ?><?= esc($territory['area_share_suffix']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold mb-3"><?= esc($territory['orbit_title']) ?></h3>
                <div class="row g-3">
                    <?php foreach ($territory['orbitations'] as $orbit): ?>
                        <div class="col-6 col-md-3">
                            <div class="small text-body-secondary"><?= esc($orbit['label']) ?></div>
                            <div class="fw-bold fs-5"><?= esc($orbit['value']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <?php foreach ($content['seasons']['items'] as $index => $season): ?>
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 h-100 musim <?= $index % 2 === 0 ? 'musim-timur' : 'musim-barat' ?>">
                        <div class="card-body p-4">
                            <span class="badge rounded-pill text-bg-light mb-2"><?= esc($season['period']) ?></span>
                            <h3 class="h5 fw-bold"><?= esc($season['title']) ?></h3>
                            <p class="mb-0"><?= esc($season['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="jemaat" class="bg-body-tertiary py-5">
        <div class="container">
            <div class="mb-4">
                <span class="badge rounded-pill badge-soft"><?= esc($census['section_badge']) ?></span>
                <h2 class="fw-bold mt-2"><?= esc($census['title']) ?></h2>
                <p class="text-body-secondary col-lg-8">
                    <?= esc($census['description']) ?> <?= (int) $census['household_count'] ?> <?= esc(trim((string) ($census['household_suffix'] ?? ''))) ?>
                    <?= number_format($totalJiwa) ?> <?= esc(trim((string) ($census['population_suffix'] ?? ''))) ?>
                    <?php if (($largestAgeGroups ?? []) !== [] && (int) ($largestAgeGroupCount ?? 0) > 0): ?>
                        <strong class="d-block mt-2">Kelompok terbanyak: <?= esc(implode(', ', $largestAgeGroups)) ?> (<?= number_format((int) $largestAgeGroupCount) ?> jiwa)</strong>
                    <?php endif; ?>
                </p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-3 small mb-3">
                        <span><span class="legend-dot bg-primary"></span> <?= esc($census['legend_male']) ?></span>
                        <span><span class="legend-dot bg-warning"></span> <?= esc($census['legend_female']) ?></span>
                    </div>

                    <?php foreach ($ageRows as $age): ?>
                        <?php
                        $maleWidth = $age['total'] > 0 ? ($age['male'] / $age['total']) * $age['share'] : 0;
                        $femaleWidth = $age['total'] > 0 ? ($age['female'] / $age['total']) * $age['share'] : 0;
                        $ariaLabel = $census['aria_age_prefix'] . $age['label'] . $census['aria_age_suffix']
                            . $census['aria_male_separator'] . $age['male'] . $census['aria_male_suffix']
                            . $age['female'] . $census['aria_female_suffix'] . $age['total'] . $census['aria_total_suffix'];
                        ?>
                        <div class="row align-items-center g-2 mb-2">
                            <div class="col-3 col-md-2 small fw-semibold"><?= esc($age['label']) ?> <?= esc($census['age_unit']) ?></div>
                            <div class="col-7 col-md-8 age-range-chart">
                                <div class="progress rounded-pill age-range-progress" style="height:18px" role="img" tabindex="0" aria-label="<?= esc($ariaLabel, 'attr') ?>">
                                    <div class="progress-bar bg-primary" style="width: <?= $maleWidth ?>%"></div>
                                    <div class="progress-bar bg-warning" style="width: <?= $femaleWidth ?>%"></div>
                                </div>
                                <div class="age-range-counts" aria-hidden="true">
                                    <span class="age-range-count age-range-count-men"><?= esc($census['legend_male']) ?><?= esc($census['count_separator']) ?><?= esc($age['male']) ?></span>
                                    <span class="age-range-count age-range-count-women"><?= esc($census['legend_female']) ?><?= esc($census['count_separator']) ?><?= esc($age['female']) ?></span>
                                </div>
                            </div>
                            <div class="col-2 text-end small fw-bold"><?= esc($age['total']) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="table-responsive mt-4">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= esc($census['table_age_heading']) ?></th>
                                    <th class="text-end"><?= esc($census['table_male_heading']) ?></th>
                                    <th class="text-end"><?= esc($census['table_female_heading']) ?></th>
                                    <th class="text-end"><?= esc($census['table_total_heading']) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ageRows as $age): ?>
                                    <tr>
                                        <td><?= esc($age['label']) ?></td>
                                        <td class="text-end"><?= esc($age['male']) ?></td>
                                        <td class="text-end"><?= esc($age['female']) ?></td>
                                        <td class="text-end fw-semibold"><?= esc($age['total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3"><?= esc($census['table_total_label']) ?></td>
                                    <td class="text-end"><?= number_format($totalJiwa) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="ibadah" class="container py-5">
        <div class="mb-4">
            <span class="badge rounded-pill badge-soft"><?= esc($worship['section_badge']) ?></span>
            <h2 class="fw-bold mt-2"><?= esc($worship['title']) ?></h2>
            <p class="text-body-secondary col-lg-8"><?= esc($worship['description']) ?></p>
        </div>

        <div class="row g-3">
            <?php foreach ($worship['items'] as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-lift border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <span class="badge rounded-pill badge-soft mb-2"><?= esc($item['day']) ?></span>
                            <h3 class="h6 fw-bold"><?= esc($item['name']) ?></h3>
                            <div class="small text-body-secondary"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= esc($item['place']) ?></div>
                            <div class="small mt-1"><?= esc($item['note']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
