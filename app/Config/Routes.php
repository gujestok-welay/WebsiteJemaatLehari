<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('profil', 'Home::profil');
$routes->get('berita', 'Home::berita');
$routes->get('berita/gambar/(:segment)', 'Home::beritaGambar/$1');
$routes->get('berita/(:segment)', 'Home::beritaDetail/$1');
$routes->get('galeri', 'Home::galeri');
$routes->get('galeri/media/(:segment)', 'Home::galeriMedia/$1');
$routes->get('galeri/(:segment)', 'Home::galeriDetail/$1');
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
    $routes->post('berita/archive/(:num)', 'AdminBerita::archive/$1', ['filter' => 'csrf']);
    $routes->post('berita/restore/(:num)', 'AdminBerita::restore/$1', ['filter' => 'csrf']);
    $routes->post('berita/delete/(:num)', 'AdminBerita::delete/$1', ['filter' => 'csrf']);

    $routes->get('galeri', 'AdminGaleri::index');
    $routes->get('galeri/create', 'AdminGaleri::create');
    $routes->get('galeri/edit/(:num)', 'AdminGaleri::edit/$1');
    $routes->get('galeri/media/(:segment)', 'AdminGaleri::media/$1');
    $routes->post('galeri/save', 'AdminGaleri::save', ['filter' => 'csrf']);
    $routes->post('galeri/archive/(:num)', 'AdminGaleri::archive/$1', ['filter' => 'csrf']);
    $routes->post('galeri/restore/(:num)', 'AdminGaleri::restore/$1', ['filter' => 'csrf']);

    $routes->get('jadwal', 'AdminJadwal::index');
    $routes->get('jadwal/create', 'AdminJadwal::create');
    $routes->get('jadwal/edit/(:num)', 'AdminJadwal::edit/$1');
    $routes->post('jadwal/save', 'AdminJadwal::save', ['filter' => 'csrf']);
    $routes->post('jadwal/cancel/(:num)', 'AdminJadwal::cancel/$1', ['filter' => 'csrf']);
    $routes->post('jadwal/reactivate/(:num)', 'AdminJadwal::reactivate/$1', ['filter' => 'csrf']);
    $routes->post('jadwal/archive/(:num)', 'AdminJadwal::archive/$1', ['filter' => 'csrf']);
    $routes->post('jadwal/restore/(:num)', 'AdminJadwal::restore/$1', ['filter' => 'csrf']);
    $routes->post('jadwal/delete/(:num)', 'AdminJadwal::delete/$1', ['filter' => 'csrf']);

    $routes->get('profil', 'AdminProfil::index');
    $routes->get('profil/kelola/(:segment)', 'AdminProfil::manage/$1');
    $routes->post('profil/save-profile', 'AdminProfil::saveProfile', ['filter' => 'csrf']);
    $routes->post('profil/save-page-content', 'AdminProfil::savePageContent', ['filter' => 'csrf']);
    $routes->post('profil/modules/territory', 'AdminProfil::saveTerritoryModule', ['filter' => 'csrf']);
    $routes->post('profil/modules/census', 'AdminProfil::saveCensusModule', ['filter' => 'csrf']);
    $routes->post('profil/modules/service-map', 'AdminProfil::saveServiceMap', ['filter' => 'csrf']);
    $routes->post('profil/save-periode', 'AdminProfil::savePeriode', ['filter' => 'csrf']);
    $routes->post('profil/activate-periode/(:num)', 'AdminProfil::activatePeriode/$1', ['filter' => 'csrf']);
    $routes->post('profil/save-anggota', 'AdminProfil::saveAnggota', ['filter' => 'csrf']);
    $routes->post('profil/delete-anggota/(:num)', 'AdminProfil::deleteAnggota/$1', ['filter' => 'csrf']);

});
