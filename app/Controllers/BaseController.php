<?php // Začátek PHP kódu.

namespace App\Controllers; // Zařadí třídu do prostoru jmen controllerů aplikace.

use CodeIgniter\Controller; // Umožní používat základní controller CI4 pod názvem Controller.

// Společný rodič controllerů, aby oba mohly používat stejné helpery.
abstract class BaseController extends Controller // Abstract nelze vytvořit přímo; extends převezme možnosti Controlleru CI4.
{ // Začátek společné třídy.
    // Helpery zpřístupní funkce pro URL a formuláře, například base_url() a csrf_field().
    protected $helpers = ['url', 'form']; // CI4 načte oba helpery; protected zpřístupní tuto vlastnost v rodině dědících tříd.
} // Konec společné třídy.
