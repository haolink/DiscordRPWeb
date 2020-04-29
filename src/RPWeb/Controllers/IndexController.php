<?php

namespace RPWeb\Controllers;

use RPWeb\Library\Controller;
use RPWeb\Model\User;

class IndexController extends Controller
{
    /**
     * Index page.
     *
     * @return void
     */
    public function index() {
        $characters = User::find('147335739921793024')->characters;

        return $this->render('Index/index.html.twig', array('characters' => $characters));
    }
}
