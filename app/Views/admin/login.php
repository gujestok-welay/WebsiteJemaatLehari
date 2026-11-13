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
    <link href="<?= base_url('assets/css/theme.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('assets/js/theme.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/theme.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin-login.js') ?>" defer></script>
</head>
<body class="login-page admin-login">
<main class="login-shell row g-0 min-vh-100">
    <section class="login-content col-12 col-md-6 d-flex align-items-center justify-content-center bg-body p-4 p-md-5" aria-labelledby="page-title">
        <div class="login-form-wrap w-100">
            <div class="theme-control login-theme-control">
                <?= view('components/theme-toggle') ?>
            </div>
            <div class="login-identity">
                <img class="login-logo" src="<?= base_url('assets/images/logo/LogoGPM.png') ?>" width="56" height="56" alt="Logo Gereja Protestan Maluku">
                <p>Media Center<br>Jemaat Leahari</p>
            </div>
            <h1 id="page-title">Selamat Datang</h1>
            <p class="login-intro">Masuk dengan memasukkan informasi di bawah ini.</p>
            <?php if (! empty($error)): ?>
                <div class="login-error alert alert-danger" id="login-error" role="alert">
                    <strong>Belum berhasil masuk</strong>
                    <p><?= esc($error) ?></p>
                </div>
            <?php endif; ?>
            <form class="login-form" method="post" action="<?= esc(url_to('AdminAuth::login')) ?>"<?= ! empty($error) ? ' aria-describedby="login-error"' : '' ?>>
                <?= csrf_field() ?>
                <div class="login-field">
                    <label class="form-label" for="email">Alamat Email</label>
                    <input class="form-control" id="email" name="email" type="email" autocomplete="username" placeholder="email@example.com" value="<?= esc(old('email') ?? '') ?>"<?= ! empty($error) ? ' aria-describedby="login-error" aria-invalid="true"' : '' ?> required>
                </div>
                <div class="login-field">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="login-password">
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" placeholder="••••••••"<?= ! empty($error) ? ' aria-describedby="login-error" aria-invalid="true"' : '' ?> required>
                        <button class="password-toggle" type="button" aria-label="Tampilkan kata sandi" aria-controls="password" hidden>
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 5.1 12 5c6.5 0 10 7 10 7a20 20 0 0 1-3 3.8M6.3 6.3A21 21 0 0 0 2 12s3.5 7 10 7a11 11 0 0 0 5.7-1.7M10 10a3 3 0 0 0 4 4"/></svg>
                        </button>
                    </div>
                </div>
                <button class="btn btn-primary btn-login" type="submit">
                    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true" hidden></span>
                    <span class="login-submit-label">Masuk</span>
                </button>
                <span class="visually-hidden login-status" role="status" aria-live="polite"></span>
            </form>
            <a class="login-public-link" href="<?= site_url('/') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 5-7 7 7 7M3 12h18"/></svg>
                <span>Kembali ke website publik</span>
            </a>
        </div>
    </section>
    <figure class="login-photo d-none d-md-block col-md-6 position-relative p-0">
        <img class="w-100 h-100 object-fit-cover" src="<?= base_url('assets/images/gereja/Gereja-tampak-samping.jpeg') ?>" width="720" height="900" alt="Gedung Gereja Jemaat Leahari tampak dari samping.">
        <div class="login-photo-overlay position-absolute top-0 start-0 w-100 h-100" aria-hidden="true"></div>
    </figure>
</main>
</body>
</html>
