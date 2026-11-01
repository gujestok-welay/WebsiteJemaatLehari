<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Berita · Media Center</title>
    <style>
        :root {
            --bg:#edf3ef; --panel:#fff; --line:#dfe8e1; --ink:#1d2a25; --muted:#586b62; --accent:#214f41; --accent-strong:#183e34; --soft:#ebf3ee; --focus:#1a73e8; --danger:#9a2d2b;
            --font:"DM Sans","Segoe UI",Arial,sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--font); background:var(--bg); color:var(--ink); }
        a,button,input,select,textarea { outline-offset:5px; }
        a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline:3px solid var(--focus); outline-offset:5px; }
        .topbar { background:var(--panel); border-bottom:1px solid var(--line); }
        .brand { display:flex; gap:12px; align-items:center; text-decoration:none; color:var(--ink); font-weight:700; }
        .brand-mark { display:grid; place-items:center; width:38px; height:38px; background:var(--accent); color:#fff; border-radius:8px; }
        .container { max-width:1200px; margin:0 auto; padding:32px 20px 64px; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:24px; }
        .page-title { margin:0; font-size:clamp(1.8rem,3vw,2.7rem); font-weight:700; }
        .toolbar { display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:20px; }
        .search-form { display:flex; gap:12px; flex-wrap:wrap; }
        .field { min-height:44px; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:#fff; }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border-radius:8px; padding:10px 16px; font-weight:600; text-decoration:none; border:1px solid var(--line); background:#fff; color:var(--ink); cursor:pointer; }
        .btn-primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:16px; border:1px solid transparent; }
        .alert-success { background:#eaf7ee; border-color:#bfe0c8; color:#1d5d3d; }
        .alert-error { background:#fff1f0; border-color:#f0c6bf; color:#8f2f2d; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:12px; }
        .table-wrap { padding:18px; overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:700px; }
        th, td { padding:12px 10px; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; }
        thead th { background:#f7faf8; font-size:0.8rem; letter-spacing:0.06em; text-transform:uppercase; color:var(--muted); }
        .status { display:inline-flex; padding:6px 10px; border-radius:999px; font-size:0.75rem; font-weight:700; }
        .status-draft { background:#f3f4f6; color:#4f5c65; }
        .status-terbit { background:#e3f5ea; color:#1e6a42; }
        .status-arsip { background:#f9f2d9; color:#865a10; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .small-btn { padding:8px 10px; min-height:36px; }
        .danger { color:var(--danger); }
        @media (max-width: 720px) { .page-head { display:block; } .toolbar { display:block; } .search-form { margin-top:12px; } }
    </style>
</head>
<body>
<header class="topbar">
    <div class="container d-flex justify-content-between align-items-center" style="padding-top:12px;padding-bottom:12px;">
        <a class="brand" href="<?= site_url('admin/dashboard') ?>">
            <span class="brand-mark" aria-hidden="true">L</span>
            <span>Media Center<br>Jemaat Leahari</span>
        </a>
        <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn" type="submit">Keluar</button></form>
    </div>
</header>
<main class="container">
    <div class="page-head">
        <div>
            <p style="margin:0 0 8px;color:var(--muted);">Konten</p>
            <h1 class="page-title">Berita</h1>
        </div>
        <a class="btn btn-primary" href="<?= site_url('admin/berita/create') ?>">+ Tambah berita</a>
    </div>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <form class="search-form" method="get" action="<?= site_url('admin/berita') ?>">
            <input class="field" type="search" name="q" value="<?= esc($term ?? '') ?>" placeholder="Cari judul berita" aria-label="Cari judul berita">
            <select class="field" name="status" aria-label="Filter status berita">
                <option value="">Semua status</option>
                <?php foreach ($statusOptions as $option): ?>
                    <option value="<?= esc($option) ?>" <?= ($status ?? '') === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Cari</button>
        </form>
        <a class="btn" href="<?= site_url('admin/dashboard') ?>">Dashboard</a>
    </div>

    <section class="card">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Ringkasan</th>
                    <th>Pengubah</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" style="color:var(--muted);padding:24px 10px;">Belum ada data berita yang cocok dengan filter saat ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <?php $statusClass = strtolower(str_replace(' ', '-', ($item['status'] ?? 'Draft'))); ?>
                        <tr>
                            <td>
                                <strong><?= esc($item['title'] ?? '') ?></strong><br>
                                <span style="color:var(--muted);font-size:0.82rem;">/<?= esc($item['slug'] ?? '') ?></span>
                            </td>
                            <td><span class="status status-<?= esc($statusClass) ?>"><?= esc($item['status'] ?? 'Draft') ?></span></td>
                            <td><?= esc($item['summary'] ?? '') ?></td>
                            <td><?= esc(($item['updated_by'] ?? $item['created_by']) ?: '-') ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn small-btn" href="<?= site_url('admin/berita/edit/' . ($item['id'] ?? 0)) ?>">Edit</a>
                                    <form method="post" action="<?= site_url('admin/berita/delete/' . ($item['id'] ?? 0)) ?>" onsubmit="return confirm('Arsipkan atau hapus berita ini?');">
                                        <?= csrf_field() ?>
                                        <button class="btn small-btn danger" type="submit">Delete</button>
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
