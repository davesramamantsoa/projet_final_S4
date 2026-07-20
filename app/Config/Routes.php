<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

// ═══════════════════════════════════════
//  ESPACE CLIENT
// ═══════════════════════════════════════
$routes->get('client',          'Client::index');
$routes->post('client/login',   'Client::login');
$routes->get('client/logout',   'Client::logout');

$routes->group('client', ['filter' => 'clientAuth'], function ($routes) {
    $routes->get('dashboard',              'Client::dashboard');
    $routes->match(['get', 'post'], 'depot',      'Client::depot');
    $routes->match(['get', 'post'], 'retrait',    'Client::retrait');
    $routes->match(['get', 'post'], 'transfert',  'Client::transfert');
    $routes->get('historique',             'Client::historique');
});

// ═══════════════════════════════════════
//  ESPACE OPÉRATEUR
// ═══════════════════════════════════════
$routes->get('operateur',         'Operateur::index');
$routes->post('operateur/login',  'Operateur::login');
$routes->get('operateur/logout',  'Operateur::logout');
$routes->match(['get', 'post'], 'operateur/creer', 'Operateur::creer');

$routes->group('operateur', ['filter' => 'operateurAuth'], function ($routes) {
    $routes->get('dashboard',                     'Operateur::dashboard');
    $routes->match(['get', 'post'], 'editer/(:num)', 'Operateur::editer/$1');
    $routes->get('types/(:num)',                  'Operateur::types/$1');
    $routes->match(['get', 'post'], 'baremes/(:num)', 'Operateur::baremes/$1');
    $routes->post('ajouterBareme/(:num)',         'Operateur::ajouterBareme/$1');
    $routes->get('supprimerBareme/(:num)',        'Operateur::supprimerBareme/$1');
    $routes->get('statistiques/(:num)',           'Operateur::statistiques/$1');
    $routes->get('clients',                       'Operateur::clients');
});
