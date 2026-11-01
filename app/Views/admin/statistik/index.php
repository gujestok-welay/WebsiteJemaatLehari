<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Statistik Agregat · Admin</title>
    <style>
        :root {
            --bg:#edf3ef; --panel:#fff; --surface:#f7faf8; --line:#dfe8e1; --ink:#1d2a25; --muted:#586b62; --accent:#214f41; --focus:#1a73e8; --danger:#9a2d2b; --font:"DM Sans","Segoe UI",Arial,sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--font); background:var(--bg); color:var(--ink); }
        a,button,input { outline-offset:5px; }
        a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline:3px solid var(--focus); outline-offset:5px; }
        .shell { max-width:1100px; margin:0 auto; padding:32px 20px 64px; }
        .topbar { background:#fff; border-bottom:1px solid var(--line); }
        .header-row { max-width:1100px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; padding:16px 20px; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:var(--ink); font-weight:700; }
        .brand-mark { width:38px;height:38px; display:grid;place-items:center;background:var(--accent);color:#fff;border-radius:8px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border-radius:8px; padding:10px 16px; background:#fff; color:var(--ink); border:1px solid var(--line); cursor:pointer; text-decoration:none; font-weight:600; }
        .btn-primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:16px; }
        .alert-success { background:#eaf7ee; border:1px solid #bfe0c8; color:#1d5d3d; }
        .alert-error { background:#fff1f0; border:1px solid #f0c6bf; color:#8f2f2d; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin:24px 0 18px; }
        .page-title { margin:0; font-size:clamp(2rem,3vw,2.8rem); }
        .card { background:#fff; border:1px solid var(--line); border-radius:16px; padding:20px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .field { width:100%; min-height:46px; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:#fff; }
        label { display:block; margin-bottom:8px; font-weight:600; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:12px 10px; border-bottom:1px solid var(--line); }
        .muted { color:var(--muted); }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .danger { color:var(--danger); }
        @media (max-width:700px){ .grid{grid-template-columns:1fr;} .page-head{display:block;} }
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
            <p class="muted" style="margin:0 0 8px;">Ringkasan Jemaat</p>
            <h1 class="page-title">Statistik Agregat</h1>
        </div>
        <a class="btn btn-primary" href="<?= site_url('admin/dashboard') ?>">Dashboard</a>
    </div>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="card" style="margin-bottom:24px;">
        <h2 style="margin-top:0;">Tambah / Ubah metric</h2>
        <form method="post" action="<?= site_url('admin/statistik/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="grid">
                <div>
                    <label for="metric_label">Label metrik</label>
                    <input class="field" id="metric_label" name="metric_label" value="<?= esc(old('metric_label') ?? '') ?>" placeholder="Contoh: Jumlah KK" required>
                </div>
                <div>
                    <label for="metric_value">Nilai</label>
                    <input class="field" id="metric_value" name="metric_value" type="number" step="0.01" value="<?= esc(old('metric_value') ?? '') ?>" placeholder="0" required>
                </div>
                <div>
                    <label for="unit_label">Keterangan / satuan</label>
                    <input class="field" id="unit_label" name="unit_label" value="<?= esc(old('unit_label') ?? '') ?>" placeholder="Contoh: KK, sektor, orang">
                </div>
                <div style="display:flex; align-items:end;">
                    <button class="btn btn-primary" type="submit">Simpan Statistik</button>
                </div>
            </div>
        </form>
    </section>

    <section class="card">
        <h2 style="margin-top:0;">Daftar statistik</h2>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Nilai</th>
                        <th>Satuan</th>
                        <th>Pembaruan Terakhir</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="muted" style="padding:18px 10px;">Belum ada data statistik agregat.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= esc($item['metric_label'] ?? '-') ?></td>
                            <td><?= esc(number_format((float) ($item['metric_value'] ?? 0), 2, ',', '.')) ?></td>
                            <td><?= esc($item['unit_label'] ?? '-') ?></td>
                            <td><?= esc($item['updated_at'] ?? '-') ?></td>
                            <td>
                                <div class="actions">
                                    <form method="post" action="<?= site_url('admin/statistik/save') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) ($item['id'] ?? 0) ?>">
                                        <input type="hidden" name="metric_label" value="<?= esc($item['metric_label'] ?? '') ?>">
                                        <input type="hidden" name="metric_value" value="<?= esc($item['metric_value'] ?? 0) ?>">
                                        <input type="hidden" name="unit_label" value="<?= esc($item['unit_label'] ?? '') ?>">
                                        <button class="btn" type="submit">Edit</button>
                                    </form>
                                    <form method="post" action="<?= site_url('admin/statistik/delete/' . (int) ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Hapus data statistik ini?');">
                                        <?= csrf_field() ?>
                                        <button class="btn danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
