<?php

namespace RPWeb\Controllers;

use RPWeb\Library\Controller;
use RPWeb\Model\User;

class IndexController extends Controller
{    
    protected static $MENU = array(
        'active' => 'index'
    );

    /**
    * Index page.
    *
    * @return void
    */
   public function index() {
       return $this->render('Index/index.html.twig');
   }
}
