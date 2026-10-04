<?php

namespace App\Controllers;

use CodeIgniter\Controller;

// Společný rodič controllerů, aby oba mohly používat stejné helpery.
abstract class BaseController extends Controller
{
    // Helpery zpřístupní funkce pro URL a formuláře, například base_url(), old() a csrf_field().
    protected $helpers = ['url', 'form'];
}
