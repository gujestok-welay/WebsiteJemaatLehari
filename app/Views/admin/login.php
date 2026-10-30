<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk · Media Center Jemaat Leahari</title>
    <link rel="preload" href="<?= base_url('assets/fonts/cormorant-garamond/latin-600-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url('assets/fonts/dm-sans/latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin-login.css') ?>" rel="stylesheet">
    <script src="<?= base_url('assets/js/admin-login.js') ?>" defer></script>
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-content" aria-labelledby="page-title">
        <div class="login-form-wrap">
            <div class="login-identity">
                <img class="login-logo" src="<?= base_url('assets/images/logo/Logo_Gereja_Protestan_Maluku.jpg') ?>" width="283" height="275" alt="Logo Gereja Protestan Maluku">
                <p>Jemaat Leahari</p>
            </div>
            <h1 id="page-title">Selamat datang di Media Center</h1>
            <p class="login-intro">Masuk dengan akun admin untuk mengelola informasi jemaat.</p>
            <?php if (! empty($error)): ?>
                <div class="login-error" id="login-error" role="alert">
                    <strong>Belum berhasil masuk</strong>
                    <p><?= esc($error) ?></p>
                </div>
            <?php endif; ?>
            <form class="login-form" method="post" action="<?= site_url('admin/login') ?>"<?= ! empty($error) ? ' aria-describedby="login-error"' : '' ?>>
                <?= csrf_field() ?>
                <div class="login-field">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" autocomplete="username" value="<?= esc(old('email') ?? '') ?>" required>
                </div>
                <div class="login-field">
                    <label class="form-label" for="password">Kata sandi</label>
                    <div class="login-password">
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Tampilkan kata sandi" aria-controls="password" hidden>
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 5.1 12 5c6.5 0 10 7 10 7a20 20 0 0 1-3 3.8M6.3 6.3A21 21 0 0 0 2 12s3.5 7 10 7a11 11 0 0 0 5.7-1.7M10 10a3 3 0 0 0 4 4"/></svg>
                        </button>
                    </div>
                </div>
                <button class="btn btn-login" type="submit">Masuk</button>
            </form>
            <a class="login-public-link" href="<?= site_url('/') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 5-7 7 7 7M3 12h18"/></svg>
                <span>Kembali ke website publik</span>
            </a>
        </div>
    </section>
    <figure class="login-photo">
        <img src="<?= base_url('assets/images/gereja/Gereja-tampak-samping.jpeg') ?>" width="720" height="900" alt="Gereja Jemaat Leahari tampak dari samping, dengan menara dan langit berawan.">
    </figure>
</main>
</body>
</html>
