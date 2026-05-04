<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/api/health', 'MenuController::health');

$routes->get('/api/menus', 'MenuController::index');
$routes->get('/api/menus/(:num)', 'MenuController::show/$1');

$routes->patch('/api/menus/(:num)/reduce-stock', 'MenuController::reduceStock/$1');

$routes->get('/api/menus/(:num)/order-history', 'MenuController::orderHistory/$1');