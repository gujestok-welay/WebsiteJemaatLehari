<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$adaFilter = $term !== '' || $hariDipilih !== 'semua' || $cancelled !== null || $archived;
$parameterDasar = [
    'q' => $term,
    'cancelled' => $cancelled,
    'archived' => $archived ? '1' : null,
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

<section class="schedule-page" aria-labelledby="schedule-page-title">
    <header class="schedule-page-heading">
        <h1 id="schedule-page-title">Jadwal Ibadah</h1>
        <div class="schedule-page-actions">
            <a class="button" href="<?= site_url('jadwal-ibadah') ?>" target="_blank" rel="noopener">Lihat di situs</a>
            <a class="button button-primary" href="<?= site_url('admin/jadwal/create') ?>" data-rm-open="#schedule-create-modal">Tambah jadwal</a>
        </div>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="metrics schedule-metrics" aria-label="Ringkasan jadwal">
        <div class="metric">
            <span class="metric-label">Semua jadwal</span>
            <strong class="metric-value"><?= number_format($hitung['semua'], 0, ',', '.') ?></strong>
            <span class="schedule-metric-note">tersimpan</span>
        </div>
        <div class="metric">
            <span class="metric-label">Jadwal aktif</span>
            <strong class="metric-value"><?= number_format($hitung['aktif'], 0, ',', '.') ?></strong>
            <span class="schedule-metric-note">belum dibatalkan</span>
        </div>
        <div class="metric">
            <span class="metric-label">Dibatalkan</span>
            <strong class="metric-value"><?= number_format($hitung['dibatalkan'], 0, ',', '.') ?></strong>
            <span class="schedule-metric-note">ditandai di daftar</span>
        </div>
        <div class="metric">
            <span class="metric-label">Diarsipkan</span>
            <strong class="metric-value"><?= number_format($hitung['arsip'], 0, ',', '.') ?></strong>
            <span class="schedule-metric-note">tersimpan di arsip</span>
        </div>
    </section>

    <section class="schedule-day-section" aria-labelledby="schedule-day-title">
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

    <section class="schedule-panel" aria-label="Daftar jadwal ibadah">
        <div class="schedule-toolbar">
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
                <button class="button" type="submit">Cari</button>
                <?php if ($adaFilter): ?>
                    <a class="schedule-reset" href="<?= site_url('admin/jadwal') ?>">Hapus filter</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="schedule-result-count" aria-live="polite">
            <?= number_format($jumlahHasil, 0, ',', '.') ?> jadwal ditampilkan
        </div>

        <?php if ($items): ?>
            <div class="schedule-table-wrap">
                <table class="schedule-table">
                    <thead>
                    <tr>
                        <th scope="col">Ibadah</th>
                        <th scope="col">Tanggal &amp; waktu</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Pelayan</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php $dibatalkan = ! empty($item['is_cancelled']); ?>
                        <?php $diarsip = ! empty($item['deleted_at']); ?>
                        <tr>
                            <td class="schedule-service-name"><?= esc($item['jenis_ibadah']) ?></td>
                            <td class="schedule-date">
                                <span><?= esc($item['tanggal_fmt']) ?></span>
                                <span><?= esc($item['waktu_fmt']) ?> WIT</span>
                            </td>
                            <td><?= esc($item['lokasi']) ?></td>
                            <td><?= esc($item['pelayan'] ?: 'Belum ditentukan') ?></td>
                            <td><span class="schedule-status <?= $diarsip ? 'is-archived' : ($dibatalkan ? 'is-cancelled' : 'is-active') ?>"><?= $diarsip ? 'Arsip' : ($dibatalkan ? 'Dibatalkan' : 'Aktif') ?></span></td>
                            <td>
                                <div class="schedule-row-actions">
                                    <?php if ($diarsip): ?>
                                        <form method="post" action="<?= site_url('admin/jadwal/restore/' . (int) $item['id']) ?>" onsubmit="return confirm('Pulihkan jadwal ini dari arsip?');">
                                            <?= csrf_field() ?>
                                            <button class="schedule-action-link" type="submit">Pulihkan</button>
                                        </form>
                                    <?php else: ?>
                                        <a class="schedule-action-link" href="<?= site_url('admin/jadwal/edit/' . (int) $item['id']) ?>">Ubah</a>
                                        <form method="post" action="<?= site_url('admin/jadwal/' . ($dibatalkan ? 'reactivate' : 'cancel') . '/' . (int) $item['id']) ?>" onsubmit="return confirm('<?= $dibatalkan ? 'Aktifkan kembali' : 'Batalkan' ?> jadwal ini?');">
                                            <?= csrf_field() ?>
                                            <button class="schedule-action-link" type="submit"><?= $dibatalkan ? 'Aktifkan' : 'Batalkan' ?></button>
                                        </form>
                                        <form method="post" action="<?= site_url('admin/jadwal/archive/' . (int) $item['id']) ?>" onsubmit="return confirm('Arsipkan jadwal ini?');">
                                            <?= csrf_field() ?>
                                            <button class="schedule-action-link is-archive" type="submit">Arsipkan</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="schedule-empty">
                <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="7" y="10" width="34" height="31" rx="4"/><path d="M15 6v8M33 6v8M7 19h34M15 27h7M15 34h17"/></svg>
                <h2><?= $adaFilter ? 'Tidak ada jadwal yang cocok' : 'Belum ada jadwal ibadah' ?></h2>
                <p><?= $adaFilter ? 'Coba ubah pencarian atau filter untuk melihat jadwal lain.' : 'Jadwal yang sudah disimpan akan muncul di sini.' ?></p>
                <?php if ($adaFilter): ?>
                    <a class="text-link" href="<?= site_url('admin/jadwal') ?>">Tampilkan semua jadwal</a>
                <?php else: ?>
                    <a class="text-link" href="<?= site_url('admin/jadwal/create') ?>" data-rm-open="#schedule-create-modal">Tambah jadwal pertama</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <p class="schedule-time-note">Waktu ditampilkan dalam Waktu Indonesia Timur (WIT). Entri yang dibatalkan tetap ditandai pada daftar.</p>
</section>

<?= $this->include('components/ui/schedule-create-modal', [
    'scheduleError' => $scheduleError ?? '',
    'returnTo' => 'admin/jadwal',
    'defaultOpen' => $scheduleModalOpen ?? false,
]) ?>
<?= $this->endSection() ?>
