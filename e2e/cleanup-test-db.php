<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$writableDirectory = $root . DIRECTORY_SEPARATOR . 'writable';
$dataDirectoryName = getenv('PLAYWRIGHT_TEST_DATA_DIRECTORY') ?: 'playwright-test-data';
$port = (int) (getenv('PLAYWRIGHT_PORT') ?: 8091);
if (! preg_match('/\Aplaywright-test-data(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?\z/', $dataDirectoryName)) {
    fwrite(STDERR, "Nama folder database tes tidak valid.\n");
    exit(1);
}
$expectedDirectory = $writableDirectory . DIRECTORY_SEPARATOR . $dataDirectoryName;

if (! is_dir($expectedDirectory)) {
    fwrite(STDOUT, "Belum ada database tes Playwright untuk dibersihkan.\n");
    exit(0);
}

if (is_link($writableDirectory)
    || is_link($expectedDirectory)
    || realpath(dirname($expectedDirectory)) !== realpath($writableDirectory)) {
    fwrite(STDERR, "Folder database Playwright harus berada langsung di writable dan bukan symlink.\n");
    exit(1);
}

$databasePath = realpath($expectedDirectory) . DIRECTORY_SEPARATOR . 'profile-modules.sqlite';
$socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.2);
if (is_resource($socket)) {
    fclose($socket);
    fwrite(STDERR, "Hentikan server Playwright sebelum menghapus database tes.\n");
    exit(1);
}

foreach ([$databasePath, $databasePath . '-journal', $databasePath . '-wal', $databasePath . '-shm'] as $file) {
    if (is_link($file) || (file_exists($file) && ! is_file($file))) {
        fwrite(STDERR, "File database Playwright harus berupa file biasa.\n");
        exit(1);
    }
}

foreach ([$databasePath, $databasePath . '-journal', $databasePath . '-wal', $databasePath . '-shm'] as $file) {
    if (is_file($file) && ! unlink($file)) {
        fwrite(STDERR, "File database Playwright tidak dapat dibersihkan: " . basename($file) . "\n");
        exit(1);
    }
}

fwrite(STDOUT, "Database tes Playwright dibersihkan.\n");
