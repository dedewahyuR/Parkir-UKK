<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/siswa', 'Siswa::mulai');
$routes->get('/', 'CekLogin::login');
$routes->post('cekLogin', 'CekLogin::validasi');
$routes->get('/logout', 'CekLogin::logout');
// $routes->get('/dashboard', 'Dashboard::index');
$routes->get('transaksi/struk/(:num)', 'Transaksi::struk/$1');
$routes->get('/dashboard', 'Transaksi::index');
$routes->post('/transaksi/masuk', 'Transaksi::masuk');
$routes->get('/transaksi/keluar/(:num)', 'Transaksi::keluar/$1');
$routes->get('/transaksi/prosesKeluar/(:num)', 'Transaksi::prosesKeluar/$1');
$routes->get('/tarif','Tarif::index');
$routes->post('tarif/simpan','Tarif::simpan');
$routes->get('tarif/edit/(:num)','Tarif::edit/$1');
$routes->post('tarif/update/(:num)','Tarif::update/$1');
$routes->get('tarif/hapus/(:num)','Tarif::hapus/$1');
$routes->get('user','User::index');
$routes->post('user/simpan','User::simpan');
$routes->get('user/hapus/(:num)','User::hapus/$1');


