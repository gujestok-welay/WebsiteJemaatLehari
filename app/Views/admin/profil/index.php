<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="profile-admin" aria-labelledby="profile-page-title">
    <header class="profile-page-heading">
        <div>
            <h1 id="profile-page-title">Profil &amp; Majelis</h1>
            <p>Kelola informasi jemaat dan susunan Majelis berdasarkan data yang sudah diverifikasi.</p>
        </div>
    </header>

    <?php if (! empty($success)): ?>
        <div class="news-alert news-alert-success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="news-alert news-alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <nav class="profile-section-nav" aria-label="Bagian Profil dan Majelis">
        <a href="#profil-form">Profil Jemaat</a>
        <a href="#periode-panel">Periode Majelis</a>
        <a href="#anggota-panel">Anggota Majelis</a>
    </nav>

    <section class="profile-section" id="profil-form" aria-labelledby="profile-form-title">
        <div class="profile-section-heading">
            <h2 id="profile-form-title">Profil Jemaat</h2>
            <p>Isi atau perbarui informasi resmi yang telah diperiksa.</p>
        </div>
        <form method="post" action="<?= site_url('admin/profil/save-profile') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($profile['id'] ?? 0) ?>">
            <div class="profile-form-grid">
                <div class="profile-field profile-field-wide">
                    <label for="history">Sejarah</label>
                    <textarea class="form-control" id="history" name="history" required><?= esc(old('history') ?? ($profile['history'] ?? '')) ?></textarea>
                </div>
                <div class="profile-field profile-field-wide">
                    <label for="vision_mission">Visi dan misi</label>
                    <textarea class="form-control" id="vision_mission" name="vision_mission" required><?= esc(old('vision_mission') ?? ($profile['vision_mission'] ?? '')) ?></textarea>
                </div>
                <div class="profile-field profile-field-wide">
                    <label for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" required><?= esc(old('address') ?? ($profile['address'] ?? '')) ?></textarea>
                </div>
                <div class="profile-field">
                    <label for="maps_url">Tautan Google Maps <span>(opsional)</span></label>
                    <input class="form-control" id="maps_url" name="maps_url" type="url" value="<?= esc(old('maps_url') ?? ($profile['maps_url'] ?? ''), 'attr') ?>" placeholder="https://maps.google.com/...">
                </div>
                <div class="profile-field">
                    <label for="contact_info">Kontak resmi</label>
                    <input class="form-control" id="contact_info" name="contact_info" value="<?= esc(old('contact_info') ?? ($profile['contact_info'] ?? ''), 'attr') ?>" required>
                </div>
            </div>
            <div class="profile-form-actions">
                <button class="button button-primary" type="submit">Simpan profil</button>
            </div>
        </form>
    </section>

    <section class="profile-section" id="periode-panel" aria-labelledby="period-panel-title">
        <div class="profile-section-heading">
            <h2 id="period-panel-title">Periode Majelis</h2>
            <p>Tambahkan periode berdasarkan rentang waktu yang benar; periode lama tetap tersimpan saat periode aktif berubah.</p>
        </div>
        <form method="post" action="<?= site_url('admin/profil/save-periode') ?>">
            <?= csrf_field() ?>
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="period_name">Nama periode</label>
                    <input class="form-control" id="period_name" name="period_name" value="<?= esc(old('period_name') ?? '', 'attr') ?>" maxlength="120" required>
                </div>
                <div class="profile-field">
                    <label for="start_date">Tanggal mulai</label>
                    <input class="form-control" id="start_date" name="start_date" type="date" value="<?= esc(old('start_date') ?? '', 'attr') ?>" required>
                </div>
                <div class="profile-field">
                    <label for="end_date">Tanggal selesai <span>(opsional)</span></label>
                    <input class="form-control" id="end_date" name="end_date" type="date" value="<?= esc(old('end_date') ?? '', 'attr') ?>">
                </div>
                <div class="profile-field">
                    <label for="is_active">Status periode</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="0">Tidak aktif</option>
                        <option value="1">Jadikan aktif</option>
                    </select>
                </div>
            </div>
            <div class="profile-form-actions">
                <button class="button button-primary" type="submit">Simpan periode</button>
            </div>
        </form>

        <div class="profile-table-wrap">
            <table class="profile-table">
                <thead>
                <tr>
                    <th scope="col">Nama periode</th>
                    <th scope="col">Mulai</th>
                    <th scope="col">Selesai</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($periods)): ?>
                    <tr><td class="profile-table-empty" colspan="5">Belum ada data periode Majelis.</td></tr>
                <?php else: ?>
                    <?php foreach ($periods as $period): ?>
                        <tr>
                            <td><?= esc($period['period_name']) ?></td>
                            <td><?= esc($period['start_date'] ?? '-') ?></td>
                            <td><?= esc($period['end_date'] ?? '-') ?></td>
                            <td>
                                <span class="profile-period-status <?= ! empty($period['is_active']) ? 'is-active' : '' ?>">
                                    <?= ! empty($period['is_active']) ? 'Aktif' : 'Tidak aktif' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (empty($period['is_active'])): ?>
                                    <form method="post" action="<?= site_url('admin/profil/activate-periode/' . (int) $period['id']) ?>">
                                        <?= csrf_field() ?>
                                        <button class="profile-action-link" type="submit">Aktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <span class="profile-current-period">Periode saat ini</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="profile-section" id="anggota-panel" aria-labelledby="member-panel-title">
        <div class="profile-section-heading">
            <h2 id="member-panel-title">Anggota Majelis</h2>
            <p>Pilih periode untuk mengelola nama, jabatan, dan foto yang telah mendapat izin.</p>
        </div>

        <form class="profile-period-filter" method="get" action="<?= site_url('admin/profil') ?>">
            <label for="period_id">Periode yang dikelola</label>
            <select class="form-select" id="period_id" name="period_id" onchange="this.form.submit()">
                <option value="">Pilih periode</option>
                <?php foreach ($periods as $period): ?>
                    <option value="<?= (int) $period['id'] ?>" <?= ((int) ($selectedPeriod ?? 0)) === (int) $period['id'] ? 'selected' : '' ?>>
                        <?= esc($period['period_name']) ?><?= ! empty($period['is_active']) ? ' · Aktif' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ((int) ($selectedPeriod ?? 0) <= 0): ?>
            <p class="profile-inline-note" role="status">Tambahkan atau pilih periode Majelis sebelum memasukkan anggota.</p>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/profil/save-anggota') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="periode_id" value="<?= esc((string) ($selectedPeriod ?? 0), 'attr') ?>">
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="member_name">Nama</label>
                    <input class="form-control" id="member_name" name="name" value="<?= esc(old('name') ?? '', 'attr') ?>" maxlength="150" required <?= (int) ($selectedPeriod ?? 0) <= 0 ? 'disabled' : '' ?>>
                </div>
                <div class="profile-field">
                    <label for="member_position">Jabatan</label>
                    <input class="form-control" id="member_position" name="position" value="<?= esc(old('position') ?? '', 'attr') ?>" maxlength="150" required <?= (int) ($selectedPeriod ?? 0) <= 0 ? 'disabled' : '' ?>>
                </div>
                <div class="profile-field profile-field-wide">
                    <label for="member_photo">Foto <span>(opsional, JPG/PNG/WEBP, maksimal 2 MB)</span></label>
                    <input class="form-control" id="member_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" <?= (int) ($selectedPeriod ?? 0) <= 0 ? 'disabled' : '' ?>>
                </div>
            </div>
            <div class="profile-form-actions">
                <button class="button button-primary" type="submit" <?= (int) ($selectedPeriod ?? 0) <= 0 ? 'disabled' : '' ?>>Simpan anggota</button>
            </div>
        </form>

        <div class="profile-table-wrap">
            <table class="profile-table">
                <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Jabatan</th>
                    <th scope="col">Foto</th>
                    <th scope="col"><span class="visually-hidden">Tindakan</span></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($members)): ?>
                    <tr><td class="profile-table-empty" colspan="4">Belum ada anggota pada periode ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= esc($member['name']) ?></td>
                            <td><?= esc($member['position']) ?></td>
                            <td>
                                <?php if (! empty($member['photo_path'])): ?>
                                    <img class="profile-member-photo" src="<?= esc(base_url($member['photo_path']), 'attr') ?>" alt="Foto <?= esc($member['name'], 'attr') ?>">
                                <?php else: ?>
                                    <span class="profile-no-photo">Belum ada foto</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="<?= site_url('admin/profil/delete-anggota/' . (int) $member['id']) ?>" onsubmit="return confirm('Hapus anggota Majelis ini?');">
                                    <?= csrf_field() ?>
                                    <button class="profile-action-link is-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?= $this->endSection() ?>
