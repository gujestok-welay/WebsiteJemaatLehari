<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Profil & Majelis · Admin</title>
    <style>
        :root {
            --bg:#edf3ef; --panel:#fff; --surface:#f7faf8; --line:#dfe8e1; --ink:#1d2a25; --muted:#586b62; --accent:#214f41; --focus:#1a73e8; --danger:#9a2d2b; --font:"DM Sans","Segoe UI",Arial,sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--font); background:var(--bg); color:var(--ink); }
        a,button,input,select,textarea { outline-offset:5px; }
        a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline:3px solid var(--focus); outline-offset:5px; }
        .shell { max-width:1200px; margin:0 auto; padding:32px 20px 64px; }
        .topbar { background:#fff; border-bottom:1px solid var(--line); }
        .header-row { max-width:1200px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; padding:16px 20px; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:var(--ink); font-weight:700; }
        .brand-mark { width:38px;height:38px; display:grid;place-items:center;background:var(--accent);color:#fff;border-radius:8px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border-radius:8px; padding:10px 16px; background:#fff; color:var(--ink); border:1px solid var(--line); cursor:pointer; text-decoration:none; font-weight:600; }
        .btn-primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:16px; }
        .alert-success { background:#eaf7ee; border:1px solid #bfe0c8; color:#1d5d3d; }
        .alert-error { background:#fff1f0; border:1px solid #f0c6bf; color:#8f2f2d; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin:24px 0 18px; }
        .page-title { margin:0; font-size:clamp(2rem,3vw,2.8rem); }
        .tabs { background:#fff; border:1px solid var(--line); border-radius:16px; padding:20px; }
        .tab-nav { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
        .tab-link { padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:600; color:var(--ink); border:1px solid var(--line); background:var(--surface); }
        .tab-link.active { background:var(--accent); color:#fff; border-color:var(--accent); }
        .panel { border:1px solid var(--line); border-radius:12px; background:var(--surface); padding:20px; }
        .panel + .panel { margin-top:18px; }
        .grid { display:grid; gap:18px; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .field, textarea, select { width:100%; min-height:46px; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:#fff; }
        textarea { min-height:170px; resize:vertical; }
        label { display:block; margin-bottom:8px; font-weight:600; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:12px 10px; border-bottom:1px solid var(--line); vertical-align:top; }
        .badge { display:inline-block; padding:6px 10px; border-radius:999px; background:#ebf3ee; color:#1f5b4e; font-weight:700; font-size:0.8rem; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .danger { color:var(--danger); }
        @media (max-width:760px){ .row{grid-template-columns:1fr;} .page-head{display:block;} }
    </style>
</head>
<body>
<header class="topbar">
    <div class="header-row">
        <a class="brand" href="<?= site_url('admin/dashboard') ?>">
            <span class="brand-mark" aria-hidden="true">L</span>
            <span>Media Center<br>Jemaat Leahari</span>
        </a>
        <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn" type="submit">Keluar</button></form>
    </div>
</header>
<main class="shell">
    <div class="page-head">
        <div>
            <p style="margin:0 0 8px;color:var(--muted);">Organisasi & Profil</p>
            <h1 class="page-title">Profil Jemaat & Majelis</h1>
        </div>
        <a class="btn btn-primary" href="<?= site_url('admin/dashboard') ?>">Dashboard</a>
    </div>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="tabs">
        <nav class="tab-nav" aria-label="Tab profil admin">
            <a class="tab-link active" href="#profil-form">Profil Jemaat</a>
            <a class="tab-link" href="#periode-panel">Periode Kepengurusan</a>
            <a class="tab-link" href="#anggota-panel">Anggota Majelis</a>
        </nav>

        <section id="profil-form" class="panel" aria-labelledby="profil-tab">
            <h2>Tab Profil Jemaat</h2>
            <form method="post" action="<?= site_url('admin/profil/save-profile') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($profile['id'] ?? 0) ?>">
                <div class="grid">
                    <div>
                        <label for="history">Sejarah</label>
                        <textarea id="history" name="history" required><?= esc(old('history') ?? ($profile['history'] ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label for="vision_mission">Visi-Misi</label>
                        <textarea id="vision_mission" name="vision_mission" required><?= esc(old('vision_mission') ?? ($profile['vision_mission'] ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label for="address">Alamat & Google Maps</label>
                        <textarea id="address" name="address" required><?= esc(old('address') ?? ($profile['address'] ?? '')) ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label for="maps_url">Tautan Google Maps</label>
                            <input class="field" id="maps_url" name="maps_url" type="url" value="<?= esc(old('maps_url') ?? ($profile['maps_url'] ?? '')) ?>" placeholder="https://maps.google.com/...">
                        </div>
                        <div>
                            <label for="contact_info">Kontak Resmi</label>
                            <input class="field" id="contact_info" name="contact_info" value="<?= esc(old('contact_info') ?? ($profile['contact_info'] ?? '')) ?>" required>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit">Simpan Profil Jemaat</button>
                    </div>
                </div>
            </form>
        </section>

        <section id="periode-panel" class="panel" aria-labelledby="periode-tab">
            <h2>Tab Periode Kepengurusan</h2>
            <form method="post" action="<?= site_url('admin/profil/save-periode') ?>">
                <?= csrf_field() ?>
                <div class="grid">
                    <div class="row">
                        <div>
                            <label for="period_name">Nama Periode</label>
                            <input class="field" id="period_name" name="period_name" value="<?= esc(old('period_name') ?? '') ?>" placeholder="Contoh: 2025-2028" required>
                        </div>
                        <div>
                            <label for="start_date">Mulai</label>
                            <input class="field" id="start_date" name="start_date" type="date" value="<?= esc(old('start_date') ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label for="end_date">Selesai</label>
                            <input class="field" id="end_date" name="end_date" type="date" value="<?= esc(old('end_date') ?? '') ?>">
                        </div>
                        <div>
                            <label for="is_active">Jadikan periode aktif</label>
                            <select class="field" id="is_active" name="is_active">
                                <option value="0">Tidak aktif</option>
                                <option value="1">Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit">Simpan Periode</button>
                    </div>
                </div>
            </form>

            <div style="margin-top:20px; overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th>Aktifkan</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($periods)): ?>
                        <tr>
                            <td colspan="5" style="color:var(--muted);">Belum ada periode kepengurusan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($periods as $period): ?>
                            <tr>
                                <td><?= esc($period['period_name']) ?></td>
                                <td><?= esc($period['start_date'] ?? '-') ?></td>
                                <td><?= esc($period['end_date'] ?? '-') ?></td>
                                <td><?= ! empty($period['is_active']) ? '<span class="badge">Aktif</span>' : 'Tidak aktif' ?></td>
                                <td>
                                    <?php if (empty($period['is_active'])): ?>
                                        <form method="post" action="<?= site_url('admin/profil/activate-periode/' . (int) $period['id']) ?>"><?= csrf_field() ?><button class="btn" type="submit">Aktifkan</button></form>
                                    <?php else: ?>
                                        <span class="badge">Sedang aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="anggota-panel" class="panel" aria-labelledby="anggotatab">
            <h2>Tab Anggota Majelis</h2>
            <form method="get" action="<?= site_url('admin/profil') ?>" style="margin-bottom:18px;">
                <label for="period_id">Filter periode</label>
                <div class="row">
                    <div>
                        <select class="field" id="period_id" name="period_id" onchange="this.form.submit()">
                            <option value="">Pilih periode</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?= (int) $period['id'] ?>" <?= ((int) ($selectedPeriod ?? 0)) === (int) $period['id'] ? 'selected' : '' ?>><?= esc($period['period_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <form method="post" action="<?= site_url('admin/profil/save-anggota') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="periode_id" value="<?= esc((string) ($selectedPeriod ?? 0)) ?>">
                <div class="grid">
                    <div class="row">
                        <div>
                            <label for="member_name">Nama</label>
                            <input class="field" id="member_name" name="name" value="<?= esc(old('name') ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="member_position">Jabatan</label>
                            <input class="field" id="member_position" name="position" value="<?= esc(old('position') ?? '') ?>" required>
                        </div>
                    </div>
                    <div>
                        <label for="member_photo">Foto</label>
                        <input class="field" id="member_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit">Simpan Anggota</button>
                    </div>
                </div>
            </form>

            <div style="margin-top:20px; overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Foto</th>
                        <th>Hapus</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="4" style="color:var(--muted);">Belum ada anggota untuk periode yang dipilih.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?= esc($member['name']) ?></td>
                                <td><?= esc($member['position']) ?></td>
                                <td>
                                    <?php if (! empty($member['photo_path'])): ?>
                                        <img src="<?= esc(base_url($member['photo_path'])) ?>" alt="<?= esc($member['name']) ?>" style="max-width:90px; border-radius:8px;">
                                    <?php else: ?>
                                        <span style="color:var(--muted);">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="<?= site_url('admin/profil/delete-anggota/' . (int) $member['id']) ?>" onsubmit="return confirm('Hapus anggota Majelis ini?');">
                                        <?= csrf_field() ?>
                                        <button class="btn danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>
</html>
