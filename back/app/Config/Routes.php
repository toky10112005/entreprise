<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Désactive le routage automatique : seules les routes déclarées sont accessibles
$routes->setAutoRoute(false);


$routes->group('manager', function (RouteCollection $routes) {
    $routes->get('besoins',          'Manager\ManagerController::index');     // liste de mes besoins (?statut=Soumise)
    $routes->get('besoins/(:num)',   'Manager\ManagerController::show/$1');   // détail + historique d'un besoin
    $routes->get('formulaire',       'Manager\ManagerController::formulaire'); // postes du département + motifs
    $routes->post('besoins',         'Manager\ManagerController::create');    // créer un besoin
    $routes->post('besoins/(:num)/envoyer', 'Manager\ManagerController::envoyer/$1');
    $routes->options('besoins',      'Manager\ManagerController::options');
    $routes->options('besoins/(:num)', 'Manager\ManagerController::options');
    $routes->options('besoins/(:num)/envoyer', 'Manager\ManagerController::options');
    $routes->options('formulaire',    'Manager\ManagerController::options');
});