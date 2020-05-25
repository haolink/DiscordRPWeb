<?php

namespace RPWeb\Controllers;

use Pecee\Http\Request;
use RPWeb\Library\Controller;
use RPWeb\Model\User;

class HelpController extends Controller
{   
    protected static $MENU = array(
        'active' => 'help'
    );

    /**
     * Help pages.
     *
     * @param string $helpPage
     * @return void
     */
    public function help(string $helpPage) {
        return $this->render('Help/' . $helpPage . '.html.twig');
    }
}
