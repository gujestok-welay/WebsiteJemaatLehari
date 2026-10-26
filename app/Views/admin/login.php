<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Media Center Jemaat Leahari</title>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        :root{--ink:#18251f;--muted:#58665f;--ground:#eef2ed;--paper:#fff;--accent:#245b46;--line:#d5ded7}
        body{min-height:100vh;background:var(--ground);color:var(--ink);font-family:system-ui,-apple-system,"Segoe UI",sans-serif}
        .login-shell{min-height:100vh;display:grid;grid-template-columns:minmax(0,1fr) minmax(340px,520px);gap:clamp(32px,8vw,120px);align-items:center;padding:clamp(24px,7vw,100px)}
        .story{max-width:660px}.mark{display:flex;align-items:center;gap:14px;font-size:1rem;font-weight:650}.mark-symbol{display:grid;place-items:center;width:48px;height:48px;background:var(--accent);color:white;font-size:1.35rem;font-weight:700}
        h1{font-size:clamp(3rem,7vw,6.5rem);line-height:.98;letter-spacing:-.055em;font-weight:650;margin:clamp(52px,10vh,100px) 0 24px;max-width:8ch}
        .story p{font-size:1.15rem;line-height:1.65;color:var(--muted);max-width:44ch}
        .form-panel{background:var(--paper);padding:clamp(26px,5vw,52px);border:1px solid var(--line)}
        h2{font-size:1.55rem;font-weight:650;letter-spacing:-.025em;margin-bottom:10px}.form-panel>p{color:var(--muted);margin-bottom:30px}
        .form-control{border-color:#9caaa0;border-radius:2px;min-height:50px}.form-control:focus{border-color:var(--accent);box-shadow:0 0 0 .2rem #245b4620}
        .btn-login{min-height:50px;width:100%;border-radius:2px;background:var(--accent);border-color:var(--accent);font-weight:600}.btn-login:hover{background:#194832;border-color:#194832}
        .public-link{display:inline-block;margin-top:24px;color:var(--accent);text-underline-offset:4px}
        @media(max-width:760px){.login-shell{grid-template-columns:1fr;align-content:center;gap:38px}.story{max-width:none}.story h1{font-size:clamp(2.8rem,13vw,4rem);margin:36px 0 14px}.story p{font-size:1rem}.form-panel{max-width:520px;width:100%}}
        @media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
<main class="login-shell">
    <section class="story" aria-labelledby="page-title">
        <div class="mark"><span class="mark-symbol" aria-hidden="true">L</span><span>Media Center<br>Jemaat Leahari</span></div>
        <h1 id="page-title">Ruang kerja Media Center</h1>
        <p>Masuk untuk mengelola informasi Jemaat Leahari melalui dashboard admin.</p>
    </section>
    <section class="form-panel" aria-labelledby="form-title">
        <h2 id="form-title">Masuk ke dashboard</h2>
        <p>Gunakan akun admin yang telah disiapkan.</p>
        <?php if (! empty($error)): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endif; ?>
        <form method="post" action="<?= site_url('admin/login') ?>">
            <?= csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" autocomplete="username" value="<?= esc(old('email') ?? '') ?>" required></div>
            <div class="mb-4"><label class="form-label" for="password">Kata sandi</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
            <button class="btn btn-primary btn-login" type="submit">Masuk</button>
        </form>
        <a class="public-link" href="<?= site_url('/') ?>">Kembali ke website publik</a>
    </section>
</main>
</body>
</html>
