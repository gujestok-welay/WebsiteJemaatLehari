<?php
$adaFilter = $term !== '' || $hariDipilih !== 'semua' || $cancelled !== null || $archived;
$parameterDasar = [
    'q' => $term,
    'cancelled' => $cancelled,
    'archived' => $archived ? '1' : null,
    'hari' => $hariDipilih === 'semua' ? null : $hariDipilih,
];
$urlFilter = static function (array $perubahan) use ($parameterDasar): string {
    $parameter = array_merge($parameterDasar, $perubahan);
    $parameter = array_filter($parameter, static fn ($nilai): bool => $nilai !== null && $nilai !== '');

    return site_url('admin/jadwal') . ($parameter ? '?' . http_build_query($parameter) : '');
};
?>
<section class="schedule-day-section" data-schedule-controls aria-labelledby="schedule-day-title">
        <div class="schedule-section-heading">
            <h2 id="schedule-day-title">Jadwal menurut hari</h2>
            <p>Pengelompokan mengikuti hari pada tanggal setiap entri.</p>
        </div>
        <nav class="schedule-days" aria-label="Filter hari">
            <?php foreach ($hari as $kunci => $dataHari): ?>
                <a class="schedule-day <?= $hariDipilih === $kunci ? 'is-selected' : '' ?> <?= $hariIni === $kunci ? 'is-today' : '' ?>"
                   href="<?= esc($urlFilter(['hari' => $kunci]), 'attr') ?>"
                   <?= $hariDipilih === $kunci ? 'aria-current="page"' : '' ?>>
                    <span class="schedule-day-name">
                        <?= esc($dataHari['label']) ?>
                        <?php if ($hariIni === $kunci): ?><span class="schedule-today-dot" aria-label="Hari ini"></span><?php endif; ?>
                    </span>
                    <strong><?= number_format($hitungHari[$kunci], 0, ',', '.') ?></strong>
                    <span class="schedule-day-unit">entri</span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>
