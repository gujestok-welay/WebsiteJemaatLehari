<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('profil', 'Home::profil');
$routes->get('berita', 'Home::berita');
$routes->get('jadwal-ibadah', 'Home::jadwal');
