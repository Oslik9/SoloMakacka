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
    'RaceYears::create'
);

$routes->post(
    'race-years',
    'RaceYears::store',
    ['filter' => 'csrf']
);
