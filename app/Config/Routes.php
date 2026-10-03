<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->addRedirect('/', 'pariz-nice');

$routes->get('pariz-nice', 'ParisNice::index');

$routes->get(
    'pariz-nice/stage/(:num)/results/(:num)',
    'ParisNice::results/$1/$2'
);

$routes->get(
    'race-years/create',
    'ParisNice::create'
);

$routes->post(
    'race-years',
    'ParisNice::store',
    ['filter' => 'csrf']
);
