<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= ! empty($item) ? 'Edit Berita' : 'Tambah Berita' ?> · Media Center</title>
    <style>
        :root {
            --bg:#edf3ef; --panel:#fff; --line:#dfe8e1; --ink:#1d2a25; --muted:#586b62; --accent:#214f41; --focus:#1a73e8; --font:"DM Sans","Segoe UI",Arial,sans-serif;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--font); background:var(--bg); color:var(--ink); }
        a,button,input,select,textarea { outline-offset:5px; }
        a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline:3px solid var(--focus); outline-offset:5px; }
        .topbar { background:var(--panel); border-bottom:1px solid var(--line); }
        .brand { display:flex; gap:12px; align-items:center; text-decoration:none; color:var(--ink); font-weight:700; }
        .brand-mark { display:block; width:38px; height:38px; flex:0 0 auto; object-fit:contain; }
        .container { max-width:980px; margin:0 auto; padding:32px 20px 64px; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:24px; }
        .page-title { margin:0; font-size:clamp(1.8rem,3vw,2.6rem); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:22px; }
        .grid { display:grid; gap:18px; }
        .field { width:100%; min-height:44px; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:#fff; }
        textarea.field { min-height:220px; resize:vertical; }
        label { display:block; margin-bottom:8px; font-weight:600; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border-radius:8px; padding:10px 16px; font-weight:600; text-decoration:none; border:1px solid var(--line); background:#fff; color:var(--ink); cursor:pointer; }
        .btn-primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:16px; border:1px solid transparent; }
        .alert-error { background:#fff1f0; border-color:#f0c6bf; color:#8f2f2d; }
        .muted { color:var(--muted); }
        @media (max-width:720px){ .row { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<header class="topbar">
    <div class="container d-flex justify-content-between align-items-center" style="padding-top:12px;padding-bottom:12px;">
        <a class="brand" href="<?= site_url('admin/dashboard') ?>">
            <img class="brand-mark" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" alt="" width="38" height="38">
            <span>Media Center<br>Jemaat Leahari</span>
        </a>
        <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn" type="submit">Keluar</button></form>
    </div>
</header>
<main class="container">
    <div class="page-head">
        <div>
            <p class="muted" style="margin:0 0 8px;">Konten</p>
            <h1 class="page-title"><?= ! empty($item) ? 'Edit Berita' : 'Tambah Berita' ?></h1>
        </div>
        <a class="btn" href="<?= site_url('admin/berita') ?>">Kembali</a>
    </div>

    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <form method="post" action="<?= site_url('admin/berita/save') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($item['id'] ?? '') ?>">

            <div class="grid">
                <div>
                    <label for="title">Judul</label>
                    <input class="field" id="title" name="title" value="<?= esc(old('title') ?? ($item['title'] ?? '')) ?>" required>
                </div>

                <div>
                    <label for="slug">Slug</label>
                    <input class="field" id="slug" name="slug" value="<?= esc(old('slug') ?? ($item['slug'] ?? '')) ?>" placeholder="otomatis dari judul jika dikosongkan">
                </div>

                <div>
                    <label for="summary">Ringkasan</label>
                    <textarea class="field" id="summary" name="summary" required><?= esc(old('summary') ?? ($item['summary'] ?? '')) ?></textarea>
                </div>

                <div>
                    <label for="content">Isi berita</label>
                    <textarea class="field" id="content" name="content" required><?= esc(old('content') ?? ($item['content'] ?? '')) ?></textarea>
                </div>

                <div class="row">
                    <div>
                        <label for="status">Status publikasi</label>
                        <select class="field" id="status" name="status" required>
                            <option value="">Pilih status</option>
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?= esc($option) ?>" <?= (old('status') ?? ($item['status'] ?? '')) === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="cover_image">Gambar kover</label>
                        <input class="field" id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
                        <?php if (! empty($item['cover_image'])): ?>
                            <div class="muted" style="margin-top:8px;">
                                Cover saat ini: <code><?= esc($item['cover_image']) ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
                    <button class="btn btn-primary" type="submit"><?= ! empty($item) ? 'Simpan Perubahan' : 'Simpan Berita' ?></button>
                    <a class="btn" href="<?= site_url('admin/berita') ?>">Batal</a>
                </div>
            </div>
        </form>
    </section>
</main>
</body>
</html>
