<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="schedule-page" data-schedule-page aria-labelledby="schedule-page-title">
    <header class="schedule-page-heading">
        <h1 id="schedule-page-title">Jadwal Ibadah</h1>
        <div class="schedule-page-actions">
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

    <?= view('admin/jadwal/_schedule-controls', [
        'term' => $term,
        'hariDipilih' => $hariDipilih,
        'cancelled' => $cancelled,
        'archived' => $archived,
        'hari' => $hari,
        'hariIni' => $hariIni,
        'hitung' => $hitung,
        'hitungHari' => $hitungHari,
    ]) ?>

    <section class="schedule-panel" aria-label="Daftar jadwal ibadah">
        <?= view('admin/jadwal/_schedule-toolbar', [
            'term' => $term,
            'hariDipilih' => $hariDipilih,
            'cancelled' => $cancelled,
            'archived' => $archived,
            'hitung' => $hitung,
        ]) ?>
        <div class="schedule-partial-error news-alert news-alert-error" data-schedule-error role="alert" hidden>
            <span>Filter gagal dimuat. Jadwal yang tampil belum berubah.</span>
            <button class="schedule-retry-link" type="button" data-schedule-retry>Coba lagi</button>
        </div>
        <p class="visually-hidden" data-schedule-loading-status role="status" aria-live="polite" aria-atomic="true"></p>
        <?= view('admin/jadwal/_schedule-results', [
            'term' => $term,
            'hariDipilih' => $hariDipilih,
            'cancelled' => $cancelled,
            'archived' => $archived,
            'items' => $items,
            'jumlahHasil' => $jumlahHasil,
        ]) ?>
    </section>

    <p class="schedule-time-note">Waktu ditampilkan dalam Waktu Indonesia Timur (WIT). Entri yang dibatalkan tetap ditandai pada daftar.</p>
</section>

<?= $this->include('components/ui/schedule-create-modal', [
    'scheduleError' => $scheduleError ?? '',
    'returnTo' => 'admin/jadwal',
    'defaultOpen' => $scheduleModalOpen ?? false,
]) ?>
<?= $this->endSection() ?>
