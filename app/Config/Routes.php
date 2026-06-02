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

// Public Zoom host shortlink — no login required
$routes->get('zoom/start/(:any)', 'MeetingController::startHost/$1');

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

// API Keys (Meeting) Routes
$routes->get('api-keys', 'ApiKeysController::index');
$routes->post('api-keys', 'ApiKeysController::create');
$routes->post('api-keys/(:num)/revoke', 'ApiKeysController::revoke/$1');

// WhatsApp Gateway Routes
$routes->get('whatsapp', 'WhatsappController::index');
$routes->post('whatsapp/pairing-code', 'WhatsappController::pairingCode');
$routes->post('whatsapp/reset-session', 'WhatsappController::resetSession');
$routes->post('whatsapp/logout', 'WhatsappController::logout');
$routes->post('whatsapp/api-keys', 'WhatsappController::createApiKey');
$routes->post('whatsapp/api-keys/(:num)/revoke', 'WhatsappController::revokeApiKey/$1');

// External API Routes
$routes->group('api', ['filter' => 'throttle'], static function ($routes) {
    // External WhatsApp API Routes
    $routes->post('whatsapp/messages', 'WhatsappApiController::createMessage');
    $routes->get('whatsapp/messages/(:num)', 'WhatsappApiController::showMessage/$1');

    // Meeting API Routes
    $routes->get('meetings', 'Api\MeetingApiController::index');
    $routes->get('meetings/conflict', 'Api\MeetingApiController::conflict');
    $routes->post('meetings', 'Api\MeetingApiController::create');
    $routes->get('meetings/(:num)', 'Api\MeetingApiController::show/$1');
    $routes->put('meetings/(:num)', 'Api\MeetingApiController::update/$1');
    $routes->patch('meetings/(:num)/approve', 'Api\MeetingApiController::approve/$1');
    $routes->delete('meetings/(:num)', 'Api\MeetingApiController::delete/$1');

    // Rooms API Routes
    $routes->get('rooms', 'Api\MeetingApiController::rooms');
});

// Default route
$routes->get('/', 'MeetingController::calendar');
$routes->get('/upcoming', 'MeetingController::upcoming');
