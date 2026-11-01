<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= ! empty($item) ? 'Edit Jadwal' : 'Tambah Jadwal' ?> · Media Center</title>
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
        .brand-mark { display:grid; place-items:center; width:38px; height:38px; background:var(--accent); color:#fff; border-radius:8px; }
        .container { max-width:980px; margin:0 auto; padding:32px 20px 64px; }
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:24px; }
        .page-title { margin:0; font-size:clamp(1.8rem,3vw,2.6rem); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:22px; }
        .grid { display:grid; gap:18px; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .field { width:100%; min-height:44px; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:#fff; }
        label { display:block; margin-bottom:8px; font-weight:600; }
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
            <span class="brand-mark" aria-hidden="true">L</span>
            <span>Media Center<br>Jemaat Leahari</span>
        </a>
        <form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn" type="submit">Keluar</button></form>
    </div>
</header>
<main class="container">
    <div class="page-head">
        <div>
            <p class="muted" style="margin:0 0 8px;">Agenda</p>
            <h1 class="page-title"><?= ! empty($item) ? 'Edit Jadwal' : 'Tambah Jadwal' ?></h1>
        </div>
        <a class="btn" href="<?= site_url('admin/jadwal') ?>">Kembali</a>
    </div>

    <?php if (! empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <form method="post" action="<?= site_url('admin/jadwal/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($item['id'] ?? '') ?>">
            <div class="grid">
                <div class="row">
                    <div>
                        <label for="jenis_ibadah">Jenis / nama ibadah</label>
                        <input class="field" id="jenis_ibadah" name="jenis_ibadah" value="<?= esc(old('jenis_ibadah') ?? ($item['jenis_ibadah'] ?? '')) ?>" required>
                    </div>
                    <div>
                        <label for="tanggal">Tanggal</label>
                        <input class="field" id="tanggal" name="tanggal" type="date" value="<?= esc(old('tanggal') ?? ($item['tanggal'] ?? '')) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="waktu">Waktu WIT</label>
                        <input class="field" id="waktu" name="waktu" type="time" value="<?= esc(old('waktu') ?? ($item['waktu'] ?? '')) ?>" required>
                    </div>
                    <div>
                        <label for="lokasi">Lokasi</label>
                        <input class="field" id="lokasi" name="lokasi" value="<?= esc(old('lokasi') ?? ($item['lokasi'] ?? '')) ?>" required>
                    </div>
                </div>

                <div>
                    <label for="pelayan">Nama pelayan (opsional)</label>
                    <input class="field" id="pelayan" name="pelayan" value="<?= esc(old('pelayan') ?? ($item['pelayan'] ?? '')) ?>" placeholder="Kosongkan bila belum ditentukan">
                </div>

                <div>
                    <label for="is_cancelled">Status jadwal</label>
                    <select class="field" id="is_cancelled" name="is_cancelled">
                        <option value="0" <?= (old('is_cancelled') ?? ($item['is_cancelled'] ?? 0)) == 0 ? 'selected' : '' ?>>Aktif</option>
                        <option value="1" <?= (old('is_cancelled') ?? ($item['is_cancelled'] ?? 0)) == 1 ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
                    <button class="btn btn-primary" type="submit"><?= ! empty($item) ? 'Simpan Perubahan' : 'Simpan Jadwal' ?></button>
                    <a class="btn" href="<?= site_url('admin/jadwal') ?>">Batal</a>
                </div>
            </div>
        </form>
    </section>
</main>
</body>
</html>
