<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Úvodní URL přesměruje na přehled Paříž–Nice.
$routes->addRedirect('/', 'pariz-nice');

// GET zobrazí všechny ročníky a jejich etapy metodou index() v controlleru ParisNice.
$routes->get('pariz-nice', 'ParisNice::index');

// Dvě (:num) přijmou číselné ID etapy a typ pořadí; $1 a $2 je předají metodě results().
$routes->get(
    'pariz-nice/stage/(:num)/results/(:num)',
    'ParisNice::results/$1/$2'
);

// GET otevře formulář pro přidání ročníku, ale žádná data neukládá.
$routes->get(
    'race-years/create',
    'RaceYears::create'
);

// POST předá formulář metodě store(); filtr csrf ověří ochranný token z formuláře.
$routes->post(
    'race-years',
    'RaceYears::store',
    ['filter' => 'csrf']
);
