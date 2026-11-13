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
$tabStatus = [
    'semua' => ['label' => 'Semua', 'filters' => ['cancelled' => null, 'archived' => null], 'count' => $hitung['semua']],
    'aktif' => ['label' => 'Aktif', 'filters' => ['cancelled' => 0, 'archived' => null], 'count' => $hitung['aktif']],
    'dibatalkan' => ['label' => 'Dibatalkan', 'filters' => ['cancelled' => 1, 'archived' => null], 'count' => $hitung['dibatalkan']],
    'arsip' => ['label' => 'Arsip', 'filters' => ['cancelled' => null, 'archived' => '1'], 'count' => $hitung['arsip']],
];
$statusTerpilih = $archived ? 'arsip' : ($cancelled === null ? 'semua' : ($cancelled === 1 ? 'dibatalkan' : 'aktif'));
?>
<div class="schedule-toolbar" data-schedule-toolbar>
    <nav class="schedule-status-tabs" aria-label="Filter status jadwal">
        <?php foreach ($tabStatus as $kunci => $tab): ?>
            <a href="<?= esc($urlFilter($tab['filters']), 'attr') ?>"
               class="<?= $statusTerpilih === $kunci ? 'is-active' : '' ?>"
               <?= $statusTerpilih === $kunci ? 'aria-current="page"' : '' ?>>
                <?= esc($tab['label']) ?><span><?= number_format($tab['count'], 0, ',', '.') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <form class="schedule-search" action="<?= site_url('admin/jadwal') ?>" method="get" role="search">
        <?php if ($cancelled !== null): ?><input type="hidden" name="cancelled" value="<?= (int) $cancelled ?>"><?php endif; ?>
        <?php if ($archived): ?><input type="hidden" name="archived" value="1"><?php endif; ?>
        <?php if ($hariDipilih !== 'semua'): ?><input type="hidden" name="hari" value="<?= esc($hariDipilih, 'attr') ?>"><?php endif; ?>
        <label class="visually-hidden" for="schedule-search-input">Cari jenis ibadah, lokasi, atau pelayan</label>
        <input id="schedule-search-input" type="search" name="q" value="<?= esc($term, 'attr') ?>" placeholder="Cari jadwal...">
        <?php if ($adaFilter): ?>
            <a class="schedule-reset" href="<?= site_url('admin/jadwal') ?>">Hapus filter</a>
        <?php endif; ?>
    </form>
</div>
