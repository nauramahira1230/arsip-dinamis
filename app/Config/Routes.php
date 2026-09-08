<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Route Utama ke Dashboard
$routes->get('/', 'ArchiveController::dashboard');

// Group Route Archives
$routes->group('archives', function ($routes) {
    $routes->get('/', 'ArchiveController::index');
    $routes->get('dashboard', 'ArchiveController::dashboard');
    $routes->get('import', 'ArchiveController::import');
    
    // Disesuaikan dengan action form pada View (processPreview dan saveBulk)
    $routes->post('processPreview', 'ArchiveController::processPreview');
    $routes->get('preview', 'ArchiveController::preview');
    $routes->post('saveBulk', 'ArchiveController::saveBulk');
});

$routes->group('kegiatan', function ($routes) {
    $routes->get('/', 'KegiatanController::index');
    $routes->get('create', 'KegiatanController::create');
    $routes->post('store', 'KegiatanController::store');
    $routes->get('edit/(:segment)', 'KegiatanController::edit/$1');
    $routes->post('update/(:segment)', 'KegiatanController::update/$1');
    $routes->post('delete/(:segment)', 'KegiatanController::delete/$1');
});