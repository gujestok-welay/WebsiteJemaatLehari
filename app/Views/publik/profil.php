<?= $this->extend('layouts/publik') ?>
<?= $this->section('content') ?>

<div class="profil-page">
<section class="profil-hero text-white">
    <div class="container py-5 position-relative">
        <span class="badge rounded-pill badge-soft mb-3">Profil Jemaat</span>
        <h1 class="display-5 fw-bold mb-3">Jemaat GPM Leahari</h1>
        <p class="lead col-lg-7 mb-4">
            Ibukota Kecamatan Leitimur Selatan, di pegunungan Kakusang, menghadap langsung ke Laut Banda,
            sebuah jemaat pesisir di sisi timur Pulau Ambon.
        </p>
        <div class="d-flex flex-wrap gap-2">
            <a href="#wilayah" class="btn btn-light rounded-pill px-4">Wilayah</a>
            <a href="#jemaat" class="btn btn-outline-light rounded-pill px-4">Data Jemaat</a>
            <a href="#ibadah" class="btn btn-outline-light rounded-pill px-4">Peribadahan</a>
        </div>
    </div>
</section>

<section class="container stat-strip">
    <div class="row g-3">
        <?php
        $stats = [
            ['bi-people', number_format($total_jiwa), 'Jiwa'],
            ['bi-house-heart', $total_kk, 'Kepala Keluarga'],
            ['bi-diagram-3', '3 Sektor · 9 Unit', 'Wilayah Pelayanan'],
            ['bi-map', number_format($luas_total, 2) . ' Ha', 'Luas Wilayah'],
        ];
        foreach ($stats as $stat): ?>
            <div class="col-6 col-lg-3">
                <div class="card card-lift border-0 shadow h-100 rounded-4">
                    <div class="card-body text-center py-4">
                        <i class="bi <?= esc($stat[0]) ?> fs-2 text-primary"></i>
                        <div class="fs-4 fw-bold mt-2"><?= esc($stat[1]) ?></div>
                        <div class="text-body-secondary small"><?= esc($stat[2]) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="wilayah" class="container py-5">
    <div class="mb-4">
        <span class="badge rounded-pill badge-soft">2.1</span>
        <h2 class="fw-bold mt-2">Gambaran Umum Wilayah Pelayanan</h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold">Status Administrasi</h3>
                    <p class="text-body-secondary">
                        Secara administratif pemerintahan, Jemaat GPM Leahari berada di pusat wilayah Kecamatan
                        Leitimur Selatan dan menjadi ibukota kecamatan tersebut, masuk dalam wilayah Negeri Leahari,
                        Kota Ambon.
                    </p>
                    <h3 class="h5 fw-bold mt-4">Topografi</h3>
                    <p class="text-body-secondary">
                        Jemaat berada di wilayah pegunungan Kakusang, di sebelah timur Pulau Ambon, pada posisi
                        lintang 128°16′0″ dan bujur 3°42′48″, dengan kondisi wilayah yang relatif datar.
                        Leahari merupakan salah satu jemaat di pinggiran Kota Ambon.
                    </p>
                    <p class="text-body-secondary mb-0">
                        Selain warga GPM, terdapat pula warga gereja denominasi lain. Meski demikian, komunikasi dan
                        relasi antaranggota tetap sangat baik, terlihat dari keterlibatan mereka dalam kegiatan jemaat.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">Batas Wilayah</h3>
                    <div class="row g-2">
                        <?php foreach ($batas as $b): ?>
                            <div class="col-6">
                                <div class="batas-item rounded-3 p-3 h-100">
                                    <i class="bi <?= esc($b['ikon']) ?> text-primary fs-4"></i>
                                    <div class="small text-body-secondary mt-1">Sebelah <?= esc($b['arah']) ?></div>
                                    <div class="fw-semibold"><?= esc($b['nama']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <?php foreach ($sektor as $s):
            $persen = round($s['luas'] / $luas_total * 100); ?>
            <div class="col-md-4">
                <div class="card card-lift border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <h3 class="h5 fw-bold mb-0">Sektor <?= esc($s['nama']) ?></h3>
                            <span class="badge rounded-pill badge-soft"><?= (int) $s['unit'] ?> unit</span>
                        </div>
                        <div class="display-6 fw-bold mt-3"><?= number_format($s['luas'], 2) ?> <small
                                class="fs-6 text-body-secondary">Ha</small></div>
                        <div class="progress mt-3" style="height:8px" role="progressbar" aria-valuenow="<?= $persen ?>"
                            aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: <?= $persen ?>%"></div>
                        </div>
                        <div class="small text-body-secondary mt-2"><?= $persen ?>% dari total wilayah</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-4">
            <h3 class="h5 fw-bold mb-3">Orbitasi</h3>
            <div class="row g-3">
                <?php foreach ($orbitasi as $o): ?>
                    <div class="col-6 col-md-3">
                        <div class="small text-body-secondary"><?= esc($o['label']) ?></div>
                        <div class="fw-bold fs-5"><?= esc($o['jarak']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="card border-0 rounded-4 h-100 musim musim-timur">
                <div class="card-body p-4">
                    <span class="badge rounded-pill text-bg-light mb-2">April – Agustus</span>
                    <h3 class="h5 fw-bold">Musim Timur</h3>
                    <p class="mb-0">
                        Angin Timur Tenggara bertiup kuat disertai curah hujan tinggi dan gelombang laut yang besar
                        karena Leahari berhadapan langsung dengan Laut Banda. Pada musim ini jemaat rentan terhadap
                        bencana alam dan pola hidup warga ikut terpengaruh.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 rounded-4 h-100 musim musim-barat">
                <div class="card-body p-4">
                    <span class="badge rounded-pill text-bg-light mb-2">Oktober – Februari</span>
                    <h3 class="h5 fw-bold">Musim Barat</h3>
                    <p class="mb-0">
                        Angin Barat Laut bertiup, kadang hujan lebat dengan angin kencang, namun tidak sebesar Musim
                        Timur.
                        Warga dapat bekerja lebih leluasa di laut, yang berdampak pada pendapatan keluarga.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="jemaat" class="bg-body-tertiary py-5">
    <div class="container">
        <div class="mb-4">
            <span class="badge rounded-pill badge-soft">2.2.1</span>
            <h2 class="fw-bold mt-2">Jumlah Kepala Keluarga dan Jiwa</h2>
            <p class="text-body-secondary col-lg-8">
                Rekapitulasi anggota jemaat berdasarkan kategori usia: <?= $total_kk ?> KK dan
                <?= number_format($total_jiwa) ?> jiwa. Kelompok usia produktif (16–45 tahun) menjadi yang terbesar.
            </p>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 small mb-3">
                    <span><span class="legend-dot bg-primary"></span> Laki-laki</span>
                    <span><span class="legend-dot bg-warning"></span> Perempuan</span>
                </div>

                <?php foreach ($usia as $u): ?>
                    <div class="row align-items-center g-2 mb-2">
                        <div class="col-3 col-md-2 small fw-semibold"><?= esc($u['label']) ?> th</div>
                        <div class="col-7 col-md-8">
                            <div class="progress rounded-pill" style="height:18px" role="progressbar"
                                aria-label="Jemaat usia <?= esc($u['label']) ?> tahun">
                                <?php
                                $lkWidth = $u['jumlah'] ? ($u['lk'] / $u['jumlah']) * $u['persen'] : 0;
                                $prWidth = $u['jumlah'] ? ($u['pr'] / $u['jumlah']) * $u['persen'] : 0;
                                ?>
                                <div class="progress-bar bg-primary" style="width: <?= $lkWidth ?>%"></div>
                                <div class="progress-bar bg-warning" style="width: <?= $prWidth ?>%"></div>
                            </div>
                        </div>
                        <div class="col-2 text-end small fw-bold"><?= $u['jumlah'] ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="table-responsive mt-4">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usia (th)</th>
                                <th class="text-end">Lk</th>
                                <th class="text-end">Pr</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usia as $u): ?>
                                <tr>
                                    <td><?= esc($u['label']) ?></td>
                                    <td class="text-end"><?= $u['lk'] ?></td>
                                    <td class="text-end"><?= $u['pr'] ?></td>
                                    <td class="text-end fw-semibold"><?= $u['jumlah'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3">Total jiwa</td>
                                <td class="text-end"><?= number_format($total_jiwa) ?></td>
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
        <span class="badge rounded-pill badge-soft">2.2.2</span>
        <h2 class="fw-bold mt-2">Pelayanan Peribadahan</h2>
        <p class="text-body-secondary col-lg-8">
            Beragam jenis ibadah sepanjang pekan menumbuhkan iman dan memperkuat persekutuan. Beberapa di antaranya
            merupakan kekhasan Jemaat Leahari.
        </p>
    </div>

    <div class="row g-3">
        <?php foreach ($ibadah as $i): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-lift border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <span class="badge rounded-pill badge-soft mb-2"><?= esc($i['hari']) ?></span>
                        <h3 class="h6 fw-bold"><?= esc($i['nama']) ?></h3>
                        <div class="small text-body-secondary"><i class="bi bi-geo-alt me-1"></i><?= esc($i['tempat']) ?>
                        </div>
                        <div class="small mt-1"><?= esc($i['ket']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
</div>

<?= $this->endSection() ?>