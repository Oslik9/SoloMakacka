<?php

use CodeIgniter\Router\RouteCollection;

// Úvodní adresa přesměruje na Paříž–Nice.
$routes->addRedirect('/', 'pariz-nice');

// Přehled ročníků a etap Paříž–Nice.
$routes->get('pariz-nice', 'ParisNice::index');

// Pořadí vybrané etapy: typ 1 v etapě, typ 4 po etapě.
$routes->get(
    'pariz-nice/stage/(:num)/results/(:num)',
    'ParisNice::results/$1/$2'
);

// Formulář pro přidání ročníku.
$routes->get(
    'race-years/create',
    'RaceYears::create'
);

// Uložení ročníku s kontrolou CSRF tokenu.
$routes->post(
    'race-years',
    'RaceYears::store',
    ['filter' => 'csrf']
);
