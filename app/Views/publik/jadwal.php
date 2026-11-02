<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>
<style>
    .jadwal-page {
        color: var(--ink);
        background: linear-gradient(180deg, #f9f4ef 0%, var(--paper) 16rem, var(--paper) 100%);
    }

    .jadwal-page .jadwal-hero {
        background: linear-gradient(135deg, #0b3d6e 0%, #1565a8 55%, #2a8bc4 100%);
        padding: 6.5rem 0 5rem;
        position: relative;
        overflow: hidden;
    }

    .jadwal-page .jadwal-hero::after {
        content: "";
        position: absolute;
        inset: auto -10% -60% auto;
        width: 60%;
        aspect-ratio: 1;
        background: radial-gradient(circle, rgba(255, 255, 255, .14), transparent 65%);
    }

    .jadwal-page .jadwal-hero .site-frame {
        position: relative;
        z-index: 1;
    }

    .jadwal-page .badge-soft {
        background: rgba(255, 255, 255, .18);
        color: #fff;
        font-weight: 600;
    }

    .jadwal-page .jadwal-body {
        margin-top: -3rem;
        position: relative;
        z-index: 2;
        padding-bottom: 4rem;
    }

    .jadwal-page .today-icon {
        width: 3rem;
        height: 3rem;
        background: rgba(var(--bs-primary-rgb), .12);
        color: var(--bs-primary);
    }

    .jadwal-page #banner-hari-ini {
        animation: jadwal-slideIn .5s ease both;
    }

    .jadwal-page .day-tabs {
        overflow-x: auto;
        scrollbar-width: none;
    }

    .jadwal-page .day-tabs::-webkit-scrollbar {
        display: none;
    }

    .jadwal-page .day-pill,
    .jadwal-page .kat-chip {
        border: 1px solid var(--bs-border-color);
        background: transparent;
        color: var(--bs-body-color);
        border-radius: 9999px;
        white-space: nowrap;
        position: relative;
        transition: background-color .25s ease, color .25s ease, border-color .25s ease, transform .2s ease;
    }

    .jadwal-page .day-pill {
        padding: .5rem 1.1rem;
        font-weight: 600;
    }

    .jadwal-page .kat-chip {
        padding: .3rem .8rem;
        font-size: .8rem;
    }

    .jadwal-page .day-pill:hover,
    .jadwal-page .kat-chip:hover {
        border-color: var(--bs-primary);
        transform: translateY(-1px);
    }

    .jadwal-page .day-pill.active,
    .jadwal-page .kat-chip.active {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    .jadwal-page .today-dot {
        display: none;
        position: absolute;
        top: .3rem;
        right: .45rem;
        width: .45rem;
        height: .45rem;
        border-radius: 50%;
        background: #10b981;
    }

    .jadwal-page .day-pill.is-today .today-dot {
        display: block;
        animation: jadwal-pulse 1.8s ease-out infinite;
    }

    .jadwal-page .jadwal-card {
        transition: transform .25s ease, box-shadow .25s ease;
        animation: jadwal-cardIn .55s cubic-bezier(.2, .7, .2, 1) both;
        animation-delay: calc(var(--i, 0) * 60ms);
        overflow: hidden;
        position: relative;
        border: 0;
        box-shadow: 0 .5rem 1.25rem rgba(17, 12, 15, 0.06) !important;
    }

    .jadwal-page .jadwal-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--bs-primary);
        transform: scaleY(0);
        transform-origin: top;
        transition: transform .3s ease;
    }

    .jadwal-page .jadwal-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, .12) !important;
    }

    .jadwal-page .jadwal-card:hover::before {
        transform: scaleY(1);
    }

    .jadwal-page .jadwal-card:hover .card-ikon {
        background: var(--bs-primary);
        color: #fff;
        transform: rotate(-6deg) scale(1.05);
    }

    .jadwal-page .card-ikon {
        width: 3rem;
        height: 3rem;
        background: rgba(var(--bs-primary-rgb), .1);
        color: var(--bs-primary);
        transition: background-color .25s ease, color .25s ease, transform .25s ease;
    }

    .jadwal-page .badge-hari {
        background: rgba(var(--bs-primary-rgb), .1);
        color: var(--bs-primary);
        font-weight: 600;
    }

    .jadwal-page .detail-box {
        background: var(--bs-tertiary-bg);
    }

    .jadwal-page .detail-toggle i {
        transition: transform .25s ease;
        display: inline-block;
    }

    .jadwal-page .detail-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .jadwal-page .jadwal-card.is-today {
        box-shadow: 0 0 0 2px #10b981, 0 .5rem 1.25rem rgba(16, 185, 129, .18) !important;
    }

    .jadwal-page .badge-today {
        display: inline-block;
        margin-bottom: .5rem;
        background: #10b981;
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 9999px;
        letter-spacing: .02em;
    }

    .jadwal-page .badge-today.d-none {
        display: none !important;
    }

    .jadwal-page .item-ibadah.hide {
        display: none;
    }

    @keyframes jadwal-cardIn {
        from {
            opacity: 0;
            transform: translateY(18px) scale(.97);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @keyframes jadwal-slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @keyframes jadwal-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, .6);
        }

        100% {
            box-shadow: 0 0 0 .5rem rgba(16, 185, 129, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .jadwal-page #banner-hari-ini,
        .jadwal-page .jadwal-card,
        .jadwal-page .jadwal-card:hover,
        .jadwal-page .jadwal-card:hover .card-ikon,
        .jadwal-page .day-pill.is-today .today-dot {
            animation: none;
        }

        .jadwal-page .jadwal-card,
        .jadwal-page .jadwal-card:hover,
        .jadwal-page .day-pill:hover {
            transform: none;
            transition: none;
        }
    }
</style>

<div class="jadwal-page">
    <section class="jadwal-hero text-white">
        <div class="site-frame position-relative">
            <span class="badge rounded-pill badge-soft mb-3">Jadwal</span>
            <h1 class="display-5 fw-bold mb-3">Jadwal Ibadah</h1>
            <p class="lead col-lg-7 mb-0">
                Ibadah dan persekutuan sepanjang pekan di Jemaat Leahari, dari Minggu sampai Jumat.
            </p>
        </div>
    </section>

    <section class="container jadwal-body">
        <div id="banner-hari-ini" class="card border-0 shadow rounded-4 mb-4 d-none">
            <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
                <div class="today-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-calendar-check fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="small text-body-secondary" id="bn-label">Hari ini</div>
                    <div class="fw-bold fs-5" id="bn-teks"></div>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="bn-lihat">Lihat jadwal hari
                    ini</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="day-tabs d-flex flex-nowrap flex-md-wrap gap-2 mb-3" role="tablist" aria-label="Pilih hari">
                    <button type="button" class="day-pill active" data-hari="semua" role="tab"
                        aria-selected="true">Semua</button>
                    <?php foreach (['minggu' => 'Minggu', 'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'] as $key => $label): ?>
                        <button type="button" class="day-pill" data-hari="<?= esc($key, 'attr') ?>" role="tab"
                            aria-selected="false">
                            <?= esc($label) ?><span class="today-dot" aria-hidden="true"></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="search" id="cari" class="form-control border-0 shadow-none"
                                placeholder="Cari ibadah atau tempat..." aria-label="Cari ibadah">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end" id="kat-filter">
                            <button type="button" class="kat-chip active" data-kat="semua">Semua kategori</button>
                            <?php $categories = array_values(array_unique(array_map(static fn(array $item): string => strtolower((string) ($item['type'] ?? 'umum')), $schedules))); ?>
                            <?php foreach ($categories as $category): ?>
                                <button type="button" class="kat-chip"
                                    data-kat="<?= esc($category, 'attr') ?>"><?= esc(ucfirst($category)) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="small text-body-secondary mb-3" id="hasil" role="status" aria-live="polite"></div>

        <div class="row g-4" id="grid-ibadah">
            <?php foreach ($schedules as $n => $item): ?>
                <?php
                $dateValue = $item['date'] ?? date('Y-m-d');
                $type = strtolower((string) ($item['type'] ?? 'umum'));
                $dayKey = strtolower(date('l', strtotime($dateValue)));
                $dayNames = ['minggu' => 'Minggu', 'senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
                $dayLabel = $dayNames[$dayKey] ?? ucfirst($type);
                $cari = strtolower(($item['name'] ?? '') . ' ' . ($item['location'] ?? '') . ' ' . ($item['note'] ?? '') . ' ' . $type);
                ?>
                <div class="col-md-6 col-lg-4 item-ibadah" data-hari="<?= esc($dayKey, 'attr') ?>"
                    data-kat="<?= esc($type, 'attr') ?>" data-cari="<?= esc($cari, 'attr') ?>">
                    <article class="card jadwal-card border-0 h-100" style="--i: <?= $n ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="card-ikon rounded-4 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-calendar-heart fs-4"></i>
                                </div>
                                <span class="badge rounded-pill badge-hari"><?= esc($dayLabel) ?></span>
                            </div>

                            <span class="badge-today d-none">Hari ini</span>
                            <h2 class="h5 fw-bold mb-1"><?= esc($item['name']) ?></h2>
                            <div class="small text-body-secondary mb-3"><?= esc(ucfirst($type)) ?></div>

                            <div class="d-flex align-items-start gap-2 small mb-2">
                                <i class="bi bi-geo-alt text-primary mt-1"></i>
                                <span><?= esc($item['location']) ?></span>
                            </div>

                            <div class="d-flex align-items-start gap-2 small mb-3">
                                <i class="bi bi-clock text-primary mt-1"></i>
                                <span><?= esc($item['time']) ?> WIT</span>
                            </div>

                            <button class="btn btn-link detail-toggle p-0 text-decoration-none small fw-semibold"
                                type="button" data-bs-toggle="collapse" data-bs-target="#detail-<?= $n ?>"
                                aria-expanded="false" aria-controls="detail-<?= $n ?>">
                                Detail <i class="bi bi-chevron-down ms-1"></i>
                            </button>
                            <div class="collapse" id="detail-<?= $n ?>">
                                <div class="detail-box rounded-3 p-3 mt-2 small">
                                    <i class="bi bi-info-circle me-1"></i><?= esc($item['note']) ?>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="kosong" class="text-center py-5 d-none">
            <i class="bi bi-calendar-x display-4 text-body-tertiary"></i>
            <p class="mt-3 mb-1 fw-semibold">Tidak ada ibadah yang cocok.</p>
            <button type="button" class="btn btn-outline-primary rounded-pill btn-sm" id="reset">Reset filter</button>
        </div>

        <p class="small text-body-secondary mt-4 mb-0">
            <i class="bi bi-info-circle me-1"></i>Waktu pelaksanaan mengikuti jadwal yang ditetapkan oleh jemaat di tiap
            sektor.
        </p>
    </section>
</div>

<script>
    (function () {
        var pills = document.querySelectorAll('.day-pill');
        var chips = document.querySelectorAll('.kat-chip');
        var items = document.querySelectorAll('.item-ibadah');
        var cari = document.getElementById('cari');
        var hasil = document.getElementById('hasil');
        var kosong = document.getElementById('kosong');
        var banner = document.getElementById('banner-hari-ini');
        if (!pills.length || !chips.length || !items.length || !cari || !hasil || !kosong || !banner) {
            return;
        }

        var state = { hari: 'semua', kat: 'semua', q: '' };
        var namaHari = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        var labelHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var today = namaHari[new Date().getDay()];

        function setActive(list, attr, val) {
            list.forEach(function (el) {
                var on = el.getAttribute(attr) === val;
                el.classList.toggle('active', on);
                if (el.hasAttribute('aria-selected')) {
                    el.setAttribute('aria-selected', on ? 'true' : 'false');
                }
            });
        }

        function render() {
            var n = 0;
            var q = state.q.trim().toLowerCase();
            items.forEach(function (el) {
                var okHari = state.hari === 'semua' || el.dataset.hari.split(' ').indexOf(state.hari) !== -1;
                var okKat = state.kat === 'semua' || el.dataset.kat === state.kat;
                var okQ = !q || (el.dataset.cari || '').indexOf(q) !== -1;
                var tampil = okHari && okKat && okQ;
                el.classList.toggle('hide', !tampil);
                if (tampil) {
                    var card = el.querySelector('.jadwal-card');
                    if (card) {
                        card.style.setProperty('--i', n);
                        card.style.animation = 'none';
                        void card.offsetWidth;
                        card.style.animation = '';
                        n++;
                    }
                }
            });
            hasil.textContent = n + ' ibadah ditampilkan';
            kosong.classList.toggle('d-none', n > 0);
        }

        pills.forEach(function (p) {
            p.addEventListener('click', function () {
                state.hari = p.dataset.hari;
                setActive(pills, 'data-hari', state.hari);
                render();
            });
        });

        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                state.kat = c.dataset.kat;
                setActive(chips, 'data-kat', state.kat);
                render();
            });
        });

        cari.addEventListener('input', function () {
            state.q = cari.value;
            render();
        });

        document.getElementById('reset').addEventListener('click', function () {
            state = { hari: 'semua', kat: 'semua', q: '' };
            cari.value = '';
            setActive(pills, 'data-hari', 'semua');
            setActive(chips, 'data-kat', 'semua');
            render();
        });

        var pillToday = document.querySelector('.day-pill[data-hari="' + today + '"]');
        if (pillToday) {
            pillToday.classList.add('is-today');
        }

        var todayItems = [];
        items.forEach(function (el) {
            if ((el.dataset.hari || '').split(' ').indexOf(today) !== -1) {
                var card = el.querySelector('.jadwal-card');
                var badge = el.querySelector('.badge-today');
                if (card) card.classList.add('is-today');
                if (badge) badge.classList.remove('d-none');
                var title = el.querySelector('h2');
                if (title) todayItems.push(title.textContent.trim());
            }
        });

        var idx = new Date().getDay();
        document.getElementById('bn-label').textContent = 'Hari ini, ' + labelHari[idx];
        document.getElementById('bn-teks').textContent = todayItems.length ? todayItems.join(' · ') : 'Tidak ada ibadah terjadwal hari ini';

        if (todayItems.length) {
            document.getElementById('bn-lihat').addEventListener('click', function () {
                state.hari = today;
                setActive(pills, 'data-hari', today);
                render();
                document.getElementById('grid-ibadah').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        } else {
            document.getElementById('bn-lihat').classList.add('d-none');
        }

        banner.classList.remove('d-none');
        render();
    })();
</script>

<?= $this->endSection() ?>