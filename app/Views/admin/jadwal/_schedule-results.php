<?php
$adaFilter = $term !== '' || $hariDipilih !== 'semua' || $cancelled !== null || $archived;
?>
<div data-schedule-results>
    <div class="schedule-result-count" tabindex="-1">
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
                        <td class="schedule-service-name" data-label="Ibadah"><?= esc($item['jenis_ibadah']) ?></td>
                        <td class="schedule-date" data-label="Tanggal &amp; waktu">
                            <span><?= esc($item['tanggal_fmt']) ?></span>
                            <span><?= esc($item['waktu_fmt']) ?> WIT</span>
                        </td>
                        <td data-label="Lokasi"><?= esc($item['lokasi']) ?></td>
                        <td data-label="Pelayan"><?= esc($item['pelayan'] ?: 'Belum ditentukan') ?></td>
                        <td data-label="Status"><span class="schedule-status <?= $diarsip ? 'is-archived' : ($dibatalkan ? 'is-cancelled' : 'is-active') ?>"><?= $diarsip ? 'Arsip' : ($dibatalkan ? 'Dibatalkan' : 'Aktif') ?></span></td>
                        <td data-label="Tindakan">
                            <div class="schedule-row-actions">
                                <?php if ($diarsip): ?>
                                    <form method="post" action="<?= site_url('admin/jadwal/restore/' . (int) $item['id']) ?>" onsubmit="return confirm('Pulihkan jadwal ini dari arsip?');">
                                        <?= csrf_field() ?>
                                        <button class="schedule-action-link" type="submit" aria-label="Pulihkan jadwal: <?= esc($item['jenis_ibadah'], 'attr') ?>" title="Pulihkan jadwal">
                                            <span class="schedule-action-label">Pulihkan</span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                                                <path d="M3 3v5h5M12 8v4l3 2" />
                                            </svg>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a class="schedule-action-link" href="<?= site_url('admin/jadwal/edit/' . (int) $item['id']) ?>" aria-label="Ubah jadwal: <?= esc($item['jenis_ibadah'], 'attr') ?>" title="Ubah jadwal">
                                        <span class="schedule-action-label">Ubah</span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                        </svg>
                                    </a>
                                    <form method="post" action="<?= site_url('admin/jadwal/' . ($dibatalkan ? 'reactivate' : 'cancel') . '/' . (int) $item['id']) ?>" onsubmit="return confirm('<?= $dibatalkan ? 'Aktifkan kembali' : 'Batalkan' ?> jadwal ini?');">
                                        <?= csrf_field() ?>
                                        <button
                                            class="schedule-action-link"
                                            type="submit"
                                            aria-label="<?= $dibatalkan ? 'Aktifkan kembali' : 'Batalkan' ?> jadwal: <?= esc($item['jenis_ibadah'], 'attr') ?>"
                                            title="<?= $dibatalkan ? 'Aktifkan kembali' : 'Batalkan' ?> jadwal"
                                        >
                                            <span class="schedule-action-label"><?= $dibatalkan ? 'Aktifkan kembali' : 'Batalkan' ?></span>
                                            <?php if ($dibatalkan): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                    <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                                                    <path d="M3 3v5h5M8 12l3 3 5-6" />
                                                </svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                    <circle cx="12" cy="12" r="9" />
                                                    <path d="m9 9 6 6m0-6-6 6" />
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                    <form method="post" action="<?= site_url('admin/jadwal/archive/' . (int) $item['id']) ?>" onsubmit="return confirm('Arsipkan jadwal ini?');">
                                        <?= csrf_field() ?>
                                        <button class="schedule-action-link is-archive" type="submit" aria-label="Arsipkan jadwal: <?= esc($item['jenis_ibadah'], 'attr') ?>" title="Arsipkan jadwal">
                                            <span class="schedule-action-label">Arsipkan</span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                <path d="M3 4h18v4H3z" />
                                                <path d="M5 8v11h14V8M10 12h4" />
                                            </svg>
                                        </button>
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
                <a class="text-link" href="<?= site_url('admin/jadwal') ?>" data-schedule-filter-link>Tampilkan semua jadwal</a>
            <?php else: ?>
                <a class="text-link" href="<?= site_url('admin/jadwal/create') ?>" data-rm-open="#schedule-create-modal">Tambah jadwal pertama</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
