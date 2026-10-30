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
$routes->post('admin/logout', 'AdminAuth::logout', ['filter' => 'csrf']);
$routes->get('admin/dashboard', 'AdminAuth::dashboard', ['filter' => 'adminAuth']);
