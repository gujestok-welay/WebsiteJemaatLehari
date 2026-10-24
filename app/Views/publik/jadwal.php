<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<div class="page-intro site-frame"><h1>Jadwal ibadah</h1><p>Temukan waktu dan tempat ibadah. Seluruh jadwal berikut adalah dummy, bukan undangan atau jadwal resmi.</p></div>
<section class="site-frame schedule-section" aria-labelledby="daftar-jadwal">
    <div class="section-heading"><h2 id="daftar-jadwal">Daftar jadwal contoh</h2><div class="schedule-filter" hidden><label for="jenis-ibadah">Jenis ibadah</label><select id="jenis-ibadah" class="form-select"><option value="semua">Semua ibadah</option><option value="minggu">Ibadah Minggu</option><option value="keluarga">Ibadah Keluarga</option></select></div></div>
    <p>Waktu ditulis dalam Waktu Indonesia Timur (WIT). Tanggal ditetapkan untuk demonstrasi, bukan jadwal mendatang otomatis.</p>
    <div class="schedule-list">
        <?php foreach ($schedules as $item): ?>
            <article class="schedule-row" data-service="<?= esc($item['type'], 'attr') ?>"><div><h3><?= esc($item['name']) ?></h3><p class="meta">Dummy · <time datetime="<?= esc($item['date'], 'attr') ?>"><?= esc($item['day']) ?></time></p></div><p class="service-time"><?= esc($item['time']) ?> <span>WIT</span></p><div><p><strong><?= esc($item['location']) ?></strong></p><p><?= esc($item['note']) ?></p></div></article>
        <?php endforeach ?>
    </div>
    <p id="hasil-jadwal" role="status" aria-live="polite">3 jadwal contoh ditampilkan.</p>
    <p class="schedule-note">Sebelum digunakan, ganti tanggal, jam, lokasi, dan keterangan dengan jadwal yang telah disahkan.</p>
</section>
<?= $this->endSection() ?>
