<?php

require_once __DIR__ . '/../../system/Test/bootstrap.php';

$dbConfig = config('Database');
$dbConfig->tests = $dbConfig->default;

$controller = new App\Controllers\Home();
$controller->initController(
    service('request'),
    service('response'),
    service('logger')
);

// 1. Test Beranda
$berandaHtml = $controller->index();
assert(strpos($berandaHtml, 'Berita jemaat') !== false, 'Beranda has Berita section');
assert(strpos($berandaHtml, 'Jadwal ibadah') !== false, 'Beranda has Jadwal section');
assert(strpos($berandaHtml, 'news-card') !== false, 'Beranda news is card-like');
assert(strpos($berandaHtml, 'news-read-more') !== false, 'Beranda has read more link');
assert(strpos($berandaHtml, 'Baca selengkapnya') !== false, 'Beranda has "Baca selengkapnya" text');
assert(strpos($berandaHtml, 'schedule-highlight') !== false, 'Beranda has schedule highlight');
assert(strpos($berandaHtml, 'schedule-badge') !== false, 'Beranda has schedule badge');
assert(strpos($berandaHtml, 'WIT') !== false, 'Beranda has WIT timezone');
echo "[PASS] Beranda renders successfully with news cards & schedule highlight.\n";

// 2. Test Jadwal Page
$jadwalHtml = $controller->jadwal();
assert(strpos($jadwalHtml, 'Daftar jadwal') !== false, 'Jadwal page has schedule list');
assert(strpos($jadwalHtml, 'schedule-row') !== false, 'Jadwal page has schedule rows');
assert(strpos($jadwalHtml, 'schedule-info') !== false, 'Jadwal page separates info column');
assert(strpos($jadwalHtml, 'schedule-time-box') !== false, 'Jadwal page separates time column');
assert(strpos($jadwalHtml, 'schedule-details') !== false, 'Jadwal page separates details column');
assert(strpos($jadwalHtml, 'schedule-location') !== false, 'Jadwal page has schedule location');
assert(strpos($jadwalHtml, 'Waktu Indonesia Timur (WIT)') !== false, 'Jadwal page specifies Waktu Indonesia Timur (WIT)');
echo "[PASS] Jadwal page renders successfully with clear column separation.\n";

// 3. Test Berita Page
$beritaHtml = $controller->berita();
assert(strpos($beritaHtml, 'Warta & kegiatan') !== false, 'Berita page has news list');
assert(strpos($beritaHtml, 'news-card') !== false, 'Berita page uses news-card style');
assert(strpos($beritaHtml, 'news-read-more') !== false, 'Berita page has "Baca selengkapnya" links');
echo "[PASS] Berita page renders successfully with news card items.\n";

// 4. Verify CSS rules
$css = file_get_contents(__DIR__ . '/../../public/assets/css/publik.css');
// Ensure NO box-shadow is added (except box-shadow: none)
$lines = explode("\n", $css);
foreach ($lines as $i => $line) {
    if (strpos($line, 'box-shadow') !== false) {
        assert(strpos($line, 'box-shadow: none') !== false, "Line " . ($i + 1) . " has box-shadow other than none: " . trim($line));
    }
}
echo "[PASS] publik.css strictly uses box-shadow: none (no shadow elevation).\n";

// Ensure focus-visible rules exist
assert(strpos($css, 'news-read-more:focus-visible') !== false, 'news-read-more has focus-visible styling');
assert(strpos($css, 'outline: 3px solid var(--purple)') !== false, 'Focus ring is solid 3px purple');
assert(strpos($css, 'outline-offset: 5px') !== false, 'Focus ring offset is 5px');
echo "[PASS] Focus-visible outline rules are intact (solid 3px purple, offset 5px).\n";

// Ensure DM Sans is used for cards
assert(strpos($css, '.news-row h3, .schedule-row h3, .schedule-highlight h3 { font-family: var(--body-font)') !== false, 'Item headings use DM Sans');
echo "[PASS] Typography correctly configured with DM Sans for all list/card items.\n";

echo "\nALL VERIFICATIONS PASSED SUCCESSFULLY!\n";
