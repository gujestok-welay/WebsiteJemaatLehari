<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$todayLabelValue = $todayLabel ?? '';
$todayLabelText = is_array($todayLabelValue)
    ? (is_scalar($todayLabelValue['label'] ?? null) ? (string) $todayLabelValue['label'] : '')
    : (is_scalar($todayLabelValue) ? (string) $todayLabelValue : '');
?>
<section class="welcome-panel" aria-labelledby="welcome-title" style="background-image: linear-gradient(to right, rgba(46, 26, 69, .95) 0%, rgba(46, 26, 69, .8) 55%, rgba(46, 26, 69, .58) 100%), url('<?= esc(base_url('assets/images/gereja/Gereja-tampak-dalam.jpg'), 'attr') ?>');">
    <div class="welcome-copy">
        <h1 id="welcome-title">Selamat datang, <?= esc($name) ?></h1>
        <p>Kelola berita, jadwal ibadah, profil, dan statistik jemaat dari satu tempat.</p>
        <div class="welcome-actions">
            <a class="button button-outline-light" href="<?= site_url('admin/berita/create') ?>" data-rm-open="#news-create-modal">Tulis berita</a>
            <a class="button button-outline-light" href="<?= site_url('admin/jadwal/create') ?>" data-rm-open="#schedule-create-modal">Tambah jadwal</a>
        </div>
    </div>
    <p class="welcome-date">
        <?= esc($todayLabelText) ?>
        <time id="admin-live-clock" aria-label="Waktu saat ini">--:--:--</time>
        <span id="admin-timezone">· Mendeteksi zona waktu…</span>
    </p>
    <script>
        (() => {
            const clock = document.getElementById('admin-live-clock');
            const timezoneLabel = document.getElementById('admin-timezone');
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const timeFormatter = new Intl.DateTimeFormat(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23',
                timeZone: timezone,
            });
            const offsetFormatter = new Intl.DateTimeFormat('en', {
                timeZone: timezone,
                timeZoneName: 'longOffset',
            });

            const updateClock = () => {
                const now = new Date();
                clock.dateTime = now.toISOString();
                clock.textContent = timeFormatter.format(now);
                const offset = offsetFormatter.formatToParts(now)
                    .find((part) => part.type === 'timeZoneName')?.value ?? 'GMT';
                timezoneLabel.textContent = `· ${offset.replace(/^GMT/, 'UTC')} (${timezone})`;
            };

            updateClock();
            window.setInterval(updateClock, 1000);
        })();
    </script>
</section>

<?php if (! empty($success)): ?>
    <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (! empty($error)): ?>
    <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
<?php endif; ?>

<section class="metrics" aria-label="Ringkasan konten">
    <div class="metric">
        <span class="metric-label">Berita terbit</span>
        <strong class="metric-value"><?= number_format($publishedNewsCount, 0, ',', '.') ?></strong>
        <a href="<?= site_url('admin/berita?status=Terbit') ?>">Lihat berita terbit</a>
    </div>
    <div class="metric">
        <span class="metric-label">Draf berita</span>
        <strong class="metric-value"><?= number_format($draftNewsCount, 0, ',', '.') ?></strong>
        <a href="<?= site_url('admin/berita?status=Draft') ?>">Lanjutkan penyuntingan</a>
    </div>
    <div class="metric">
        <span class="metric-label">Jadwal mendatang</span>
        <strong class="metric-value"><?= number_format($upcomingScheduleCount, 0, ',', '.') ?></strong>
        <a href="<?= site_url('admin/jadwal') ?>">Kelola jadwal</a>
    </div>
</section>

<div class="dashboard-grid">
    <section class="content-panel news-panel" aria-labelledby="news-title">
        <div class="panel-heading">
            <div>
                <h2 id="news-title">Berita terbaru</h2>
                <p>Perubahan dan status konten yang tersimpan.</p>
            </div>
            <a class="text-link" href="<?= site_url('admin/berita') ?>">Semua berita</a>
        </div>

        <?php if (empty($latestNews)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M12 8h19l7 7v25H12a3 3 0 0 1-3-3V11a3 3 0 0 1 3-3Z" />
                    <path d="M30 8v8h8M17 23h16M17 29h16M17 35h10" />
                </svg>
                <h3>Belum ada berita</h3>
                <p>Berita yang disimpan akan tampil di sini.</p>
                <a class="text-link" href="<?= site_url('admin/berita/create') ?>" data-rm-open="#news-create-modal">Buat berita pertama</a>
            </div>
        <?php else: ?>
            <ul class="news-list">
                <?php foreach ($latestNews as $item): ?>
                    <?php $status = (string) ($item['status'] ?? ''); ?>
                    <li class="news-item">
                        <div class="news-item-copy">
                            <button
                                class="news-title"
                                type="button"
                                data-rm-open="#news-create-modal"
                                aria-label="Ubah berita: <?= esc($item['title'] ?? '', 'attr') ?>"
                                data-news-edit="<?= esc(json_encode([
                                    'id' => (int) ($item['id'] ?? 0),
                                    'title' => (string) ($item['title'] ?? ''),
                                    'slug' => (string) ($item['slug'] ?? ''),
                                    'summary' => (string) ($item['summary'] ?? ''),
                                    'content' => (string) ($item['content'] ?? ''),
                                    'status' => (string) ($item['status'] ?? ''),
                                    'cover_image' => (string) ($item['cover_image'] ?? ''),
                                ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), 'attr') ?>"
                            ><?= esc($item['title']) ?></button>
                            <span class="news-date"><?= esc($item['date_label'] ?? 'Tanggal belum tersedia') ?></span>
                        </div>
                        <span class="status-badge status-<?= strtolower(esc($status, 'attr')) ?>"><?= esc($status) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="content-panel schedule-panel" aria-labelledby="schedule-title">
        <div class="panel-heading">
            <div>
                <h2 id="schedule-title">Jadwal berikutnya</h2>
                <p>Agenda tersimpan yang belum dibatalkan.</p>
            </div>
            <a class="text-link" href="<?= site_url('admin/jadwal') ?>">Semua jadwal</a>
        </div>

        <?php if ($nextSchedule === null): ?>
            <div class="empty-state empty-state-compact">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <rect x="8" y="11" width="32" height="29" rx="4" />
                    <path d="M16 7v8M32 7v8M8 19h32M17 27h5M17 33h14" />
                </svg>
                <h3>Belum ada jadwal mendatang</h3>
                <p>Tambahkan jadwal jika informasi resminya sudah tersedia.</p>
                <a class="text-link" href="<?= site_url('admin/jadwal/create') ?>" data-rm-open="#schedule-create-modal">Tambah jadwal</a>
            </div>
        <?php else: ?>
            <div class="next-event">
                <p class="event-date"><?= esc($nextSchedule['date_label']) ?></p>
                <h3><?= esc($nextSchedule['jenis_ibadah']) ?></h3>
                <dl>
                    <div>
                        <dt>Waktu</dt>
                        <dd><?= esc($nextSchedule['time_label']) ?> WIT</dd>
                    </div>
                    <div>
                        <dt>Lokasi</dt>
                        <dd><?= esc($nextSchedule['lokasi']) ?></dd>
                    </div>
                </dl>
                <a class="text-link" href="<?= site_url('admin/jadwal') ?>">Periksa semua jadwal</a>
            </div>
        <?php endif; ?>
    </section>
</div>
<?= $this->include('components/ui/schedule-create-modal', [
    'scheduleError' => $scheduleError ?? '',
    'returnTo' => 'admin/dashboard',
]) ?>
<?= $this->include('components/ui/news-create-modal', [
    'newsError' => $newsError ?? '',
    'returnTo' => 'admin/dashboard',
    'defaultOpen' => $newsModalOpen ?? false,
    'statusOptions' => $statusOptions,
]) ?>
<?= $this->endSection() ?>