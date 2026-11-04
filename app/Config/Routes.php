<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('profil', 'Home::profil');
$routes->get('berita', 'Home::berita');
$routes->get('jadwal-ibadah', 'Home::jadwal');

$routes->get('admin', 'AdminAuth::index');
$routes->get('admin/login', 'AdminAuth::loginForm');
$routes->post('admin/login', 'AdminAuth::login', ['filter' => 'csrf']);

$routes->group('admin', ['filter' => 'adminAuth'], static function ($routes): void {
    $routes->get('dashboard', 'AdminAuth::dashboard');
    $routes->post('logout', 'AdminAuth::logout', ['filter' => 'csrf']);

    $routes->get('berita', 'AdminBerita::index');
    $routes->get('berita/create', 'AdminBerita::create');
    $routes->post('berita/save', 'AdminBerita::save', ['filter' => 'csrf']);
    $routes->post('berita/delete/(:num)', 'AdminBerita::delete/$1', ['filter' => 'csrf']);

    $routes->get('jadwal', 'AdminJadwal::index');
    $routes->get('jadwal/create', 'AdminJadwal::create');
    $routes->get('jadwal/edit/(:num)', 'AdminJadwal::edit/$1');
    $routes->post('jadwal/save', 'AdminJadwal::save', ['filter' => 'csrf']);
    $routes->post('jadwal/delete/(:num)', 'AdminJadwal::delete/$1', ['filter' => 'csrf']);

    $routes->get('profil', 'AdminProfil::index');
    $routes->post('profil/save-profile', 'AdminProfil::saveProfile', ['filter' => 'csrf']);
    $routes->post('profil/save-periode', 'AdminProfil::savePeriode', ['filter' => 'csrf']);
    $routes->post('profil/activate-periode/(:num)', 'AdminProfil::activatePeriode/$1', ['filter' => 'csrf']);
    $routes->post('profil/save-anggota', 'AdminProfil::saveAnggota', ['filter' => 'csrf']);
    $routes->post('profil/delete-anggota/(:num)', 'AdminProfil::deleteAnggota/$1', ['filter' => 'csrf']);

    $routes->get('statistik', 'AdminStatistik::index');
    $routes->post('statistik/save', 'AdminStatistik::save', ['filter' => 'csrf']);
    $routes->post('statistik/delete/(:num)', 'AdminStatistik::delete/$1', ['filter' => 'csrf']);
});
