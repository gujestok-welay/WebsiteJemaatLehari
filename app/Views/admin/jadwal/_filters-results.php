<?php
$scheduleData = [
    'term' => $term,
    'hariDipilih' => $hariDipilih,
    'cancelled' => $cancelled,
    'archived' => $archived,
    'hari' => $hari,
    'hariIni' => $hariIni,
    'hitung' => $hitung,
    'hitungHari' => $hitungHari,
    'items' => $items,
    'jumlahHasil' => $jumlahHasil,
];
?>
<?= view('admin/jadwal/_schedule-controls', $scheduleData) ?>
<?= view('admin/jadwal/_schedule-toolbar', $scheduleData) ?>
<?= view('admin/jadwal/_schedule-results', $scheduleData) ?>
