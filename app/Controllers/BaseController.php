<?php

namespace App\Controllers;

use CodeIgniter\Controller;

// Společný controller s helpery pro URL a formuláře.
abstract class BaseController extends Controller
{

    protected $helpers = ['url', 'form'];
}
