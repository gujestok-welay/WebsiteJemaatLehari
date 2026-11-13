<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$expectedDirectory = $root . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'playwright-test-data';
$writableDirectory = $root . DIRECTORY_SEPARATOR . 'writable';
$databasePath = getenv('PLAYWRIGHT_TEST_DATABASE_PATH');
$email = getenv('PLAYWRIGHT_TEST_ADMIN_EMAIL');
$password = getenv('PLAYWRIGHT_TEST_ADMIN_PASSWORD');

if (! extension_loaded('sqlite3')) {
    fwrite(STDERR, "Ekstensi SQLite3 PHP diperlukan untuk tes Playwright.\n");
    exit(1);
}

if (! is_dir($expectedDirectory) && ! mkdir($expectedDirectory, 0775, true) && ! is_dir($expectedDirectory)) {
    fwrite(STDERR, "Folder database khusus Playwright tidak dapat dibuat.\n");
    exit(1);
}

if (is_link($writableDirectory)
    || is_link($expectedDirectory)
    || realpath(dirname($expectedDirectory)) !== realpath($writableDirectory)) {
    fwrite(STDERR, "Folder database Playwright harus berada langsung di writable dan bukan symlink.\n");
    exit(1);
}

$expectedPath = $expectedDirectory . DIRECTORY_SEPARATOR . 'profile-modules.sqlite';
if (! is_string($databasePath)
    || realpath(dirname($databasePath)) !== realpath($expectedDirectory)
    || basename($databasePath) !== 'profile-modules.sqlite') {
    fwrite(STDERR, "Path database Playwright tidak cocok dengan lokasi data tes yang diizinkan.\n");
    exit(1);
}

if (is_link($databasePath) || (file_exists($databasePath) && ! is_file($databasePath))) {
    fwrite(STDERR, "Database tes harus berupa file biasa, bukan symlink atau direktori.\n");
    exit(1);
}

if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
    fwrite(STDERR, "Kredensial admin tes Playwright belum dikonfigurasi.\n");
    exit(1);
}

$db = new SQLite3($expectedPath);
$db->enableExceptions(true);
$db->exec('PRAGMA foreign_keys = OFF');

foreach ([
    'admin_users',
    'admin_login_attempts',
    'berita',
    'profil_jemaat',
    'majelis_periode',
    'majelis_anggota',
    'jadwal_ibadah',
] as $table) {
    $db->exec('DROP TABLE IF EXISTS "' . $table . '"');
}

$schema = [
    'CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(191) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(32) NOT NULL DEFAULT "operator",
        is_active INTEGER NOT NULL DEFAULT 1,
        deleted_at DATETIME NULL,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )',
    'CREATE TABLE admin_login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        identity_hash CHAR(64) NOT NULL,
        attempted_at INTEGER NOT NULL
    )',
    'CREATE TABLE berita (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL DEFAULT "",
        slug VARCHAR(255) NOT NULL DEFAULT "",
        summary TEXT NULL,
        content TEXT NULL,
        status VARCHAR(32) NOT NULL DEFAULT "Draft",
        archived_from_status VARCHAR(32) NULL,
        cover_image VARCHAR(255) NULL,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        deleted_at DATETIME NULL
    )',
    'CREATE TABLE profil_jemaat (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        history TEXT NULL,
        vision_mission TEXT NULL,
        address TEXT NULL,
        maps_url VARCHAR(255) NULL,
        contact_info TEXT NULL,
        page_content TEXT NULL,
        territory_content TEXT NULL,
        census_content TEXT NULL,
        service_map_path VARCHAR(255) NULL,
        service_map_title VARCHAR(150) NULL,
        service_map_alt VARCHAR(255) NULL,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        deleted_at DATETIME NULL
    )',
    'CREATE TABLE majelis_periode (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        period_name VARCHAR(120) NOT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        is_active INTEGER NOT NULL DEFAULT 0,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        deleted_at DATETIME NULL
    )',
    'CREATE TABLE majelis_anggota (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        periode_id INTEGER NOT NULL,
        name VARCHAR(150) NOT NULL,
        position VARCHAR(150) NOT NULL,
        photo_path VARCHAR(255) NULL,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        deleted_at DATETIME NULL
    )',
    'CREATE TABLE jadwal_ibadah (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        jenis_ibadah VARCHAR(120) NOT NULL,
        tanggal DATE NOT NULL,
        waktu TIME NOT NULL,
        lokasi VARCHAR(255) NOT NULL,
        pelayan VARCHAR(255) NULL,
        is_cancelled INTEGER NOT NULL DEFAULT 0,
        created_by INTEGER NULL,
        updated_by INTEGER NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        deleted_at DATETIME NULL
    )',
];

foreach ($schema as $statement) {
    $db->exec($statement);
}

$adminStmt = $db->prepare('INSERT INTO admin_users (name, email, password_hash, role, is_active, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
$adminStmt->bindValue(':name', 'Playwright Test Admin', SQLITE3_TEXT);
$adminStmt->bindValue(':email', $email, SQLITE3_TEXT);
$adminStmt->bindValue(':password_hash', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
$adminStmt->bindValue(':role', 'super_admin', SQLITE3_TEXT);
$adminStmt->execute();
$db->close();

fwrite(STDOUT, "Database Playwright khusus tes berhasil disiapkan.\n");