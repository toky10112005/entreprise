<?php
use CodeIgniter\Router\RouteCollection;

use App\Controllers\EtudiantController;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index'); 
$routes->get('/', 'EtudiantController::index');
$routes->get('/etudiant/(:num)','EtudiantController::show/$1');
