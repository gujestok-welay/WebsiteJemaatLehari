<?php

$requiredFiles = [
    __DIR__ . '/../../app/Controllers/AdminProfil.php',
    __DIR__ . '/../../app/Controllers/AdminStatistik.php',
    __DIR__ . '/../../app/Models/ProfilJemaatModel.php',
    __DIR__ . '/../../app/Models/MajelisPeriodeModel.php',
    __DIR__ . '/../../app/Models/MajelisAnggotaModel.php',
    __DIR__ . '/../../app/Models/StatistikAgregatModel.php',
    __DIR__ . '/../../app/Config/Routes.php',
];

foreach ($requiredFiles as $filePath) {
    if (! is_file($filePath)) {
        fwrite(STDERR, "MISSING FILE: {$filePath}\n");
        exit(1);
    }
}

$routeContent = file_get_contents(__DIR__ . '/../../app/Config/Routes.php');
if ($routeContent === false) {
    fwrite(STDERR, "Unable to read routes file.\n");
    exit(1);
}

foreach (['admin/profil', 'admin/statistik', 'admin/users', 'adminAuth', 'superAdmin'] as $needle) {
    if (strpos($routeContent, $needle) === false) {
        fwrite(STDERR, "RBAC ERROR: missing route assertion for {$needle}\n");
        exit(1);
    }
}

$filesToCheck = [
    __DIR__ . '/../../app/Controllers/AdminProfil.php',
    __DIR__ . '/../../app/Controllers/AdminStatistik.php',
    __DIR__ . '/../../app/Models/ProfilJemaatModel.php',
    __DIR__ . '/../../app/Models/MajelisPeriodeModel.php',
    __DIR__ . '/../../app/Models/MajelisAnggotaModel.php',
    __DIR__ . '/../../app/Models/StatistikAgregatModel.php',
];

foreach ($filesToCheck as $path) {
    $contents = file_get_contents($path);
    if ($contents === false) {
        fwrite(STDERR, "Unable to read {$path}\n");
        exit(1);
    }

    if (strpos($contents, 'created_by') === false || strpos($contents, 'updated_by') === false) {
        fwrite(STDERR, "RBAC ERROR: {$path} does not capture login-based audit fields.\n");
        exit(1);
    }
}

echo "RBAC baseline checks passed.\n";
