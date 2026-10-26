<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Media Center Jemaat Leahari</title>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        :root{--ink:#18251f;--muted:#58665f;--ground:#eef2ed;--paper:#fff;--accent:#245b46;--line:#d5ded7}
        body{background:var(--ground);color:var(--ink);font-family:system-ui,-apple-system,"Segoe UI",sans-serif}.topbar{background:var(--paper);border-bottom:1px solid var(--line)}
        .brand{display:flex;align-items:center;gap:12px;font-weight:650;color:var(--ink);text-decoration:none}.mark{display:grid;place-items:center;width:40px;height:40px;background:var(--accent);color:white;font-weight:700}
        main{max-width:1080px;margin:0 auto;padding:clamp(32px,7vw,80px) 24px}h1{font-size:clamp(2rem,4vw,3.25rem);letter-spacing:-.045em;font-weight:650;margin-bottom:14px}.lede{font-size:1.1rem;color:var(--muted);max-width:56ch}
        .workspace{margin-top:44px;background:var(--paper);border:1px solid var(--line);padding:clamp(24px,5vw,48px);display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center}.workspace h2{font-size:1.4rem;font-weight:650}.workspace p{color:var(--muted);margin-bottom:0}.status{padding:8px 12px;background:#e4eee7;color:#214d37}
        .btn-logout{border-radius:2px;border:1px solid var(--line);color:var(--ink)}.btn-logout:hover{background:var(--ground)}@media(max-width:600px){.workspace{grid-template-columns:1fr}.top-inner{align-items:flex-start!important;gap:16px}.status{justify-self:start}}
    </style>
</head>
<body>
<header class="topbar"><div class="container-fluid px-4 py-3 d-flex justify-content-between align-items-center top-inner"><a class="brand" href="<?= site_url('admin/dashboard') ?>"><span class="mark" aria-hidden="true">L</span><span>Media Center<br>Jemaat Leahari</span></a><form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn btn-logout" type="submit">Keluar</button></form></div></header>
<main>
    <h1>Dashboard Media Center</h1><p class="lede">Selamat datang, <?= esc($name) ?>. Area admin untuk pengelolaan website Jemaat Leahari.</p>
    <section class="workspace" aria-labelledby="workspace-title"><div><h2 id="workspace-title">Ruang kerja siap</h2><p>Autentikasi admin aktif. Fitur pengelolaan konten belum termasuk dalam tahap ini.</p></div><span class="status">Sesi admin aktif</span></section>
</main>
</body>
</html>
