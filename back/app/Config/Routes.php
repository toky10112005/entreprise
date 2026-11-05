<?php
use CodeIgniter\Router\RouteCollection;

use App\Controllers\EtudiantController;

/**
 * @var RouteCollection $routes
 */

$routes->group('api',static function($r){
    $r->post('login', 'AuthController::login');
    $r->post('register', 'AuthController::register');
    $r->post('logout', 'AuthController::logout');
});


$routes->group('api/manager', ['filter' => 'role:manager'], static function ($r) {
    
});