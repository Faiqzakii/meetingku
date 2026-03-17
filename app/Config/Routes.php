<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Auth Routes
$routes->get('auth/login', 'AuthController::login');
$routes->post('auth/login', 'AuthController::attemptLogin');
$routes->get('auth/logout', 'AuthController::logout');

// Meeting Routes
$routes->get('meeting/calendar', 'MeetingController::calendar');
$routes->get('meeting/upcoming', 'MeetingController::upcoming');
$routes->get('meeting/all', 'MeetingController::all');
$routes->post('meeting/create', 'MeetingController::create');
$routes->get('meeting/edit/(:num)', 'MeetingController::edit/$1');
$routes->post('meeting/update/(:num)', 'MeetingController::update/$1');
$routes->post('meeting/delete/(:num)', 'MeetingController::delete/$1');
$routes->post('meeting/status/(:num)', 'MeetingController::updateStatus/$1');
$routes->post('meeting/send-zoom/(:num)', 'MeetingController::sendZoom/$1');
$routes->post('meeting/refresh-zoom/(:num)', 'MeetingController::refreshZoom/$1');
$routes->post('meeting/manual-zoom/(:num)', 'MeetingController::updateManualZoomJoin/$1');

// Pegawai Routes
$routes->get('pegawai', 'PegawaiController::index');
$routes->post('pegawai/create', 'PegawaiController::create');
$routes->get('pegawai/edit/(:num)', 'PegawaiController::edit/$1');
$routes->put('pegawai/update/(:num)', 'PegawaiController::update/$1');
$routes->post('pegawai/update/(:num)', 'PegawaiController::update/$1'); // Keep POST for backward compatibility
$routes->post('pegawai/delete/(:num)', 'PegawaiController::delete/$1');
$routes->post('pegawai/import', 'PegawaiController::import');
$routes->get('pegawai/downloadTemplate', 'PegawaiController::downloadTemplate');

// Ruangan Routes
$routes->get('ruangan', 'RuanganController::index');
$routes->post('ruangan/create', 'RuanganController::create');
$routes->get('ruangan/edit/(:num)', 'RuanganController::edit/$1');
$routes->post('ruangan/update/(:num)', 'RuanganController::update/$1');
$routes->post('ruangan/delete/(:num)', 'RuanganController::delete/$1');
// Ruangan meetings (daily filter)
$routes->get('ruangan/(:num)/meetings', 'RuanganController::meetings/$1');

// Default route
$routes->get('/', 'MeetingController::calendar');
$routes->get('/upcoming', 'MeetingController::upcoming');
