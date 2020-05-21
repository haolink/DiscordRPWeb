<?php

namespace RPWeb\Controllers;

use RPWeb\Library\Controller;
use RPWeb\Model\Character;

class DashboardController extends Controller
{
    protected static $MENU = array(
        'active' => 'characters'
    );

    /**
     * Index page.
     *
     * @return void
     */
    public function index() {
        $characters = Character::where('user_id', $this->getUser()->getId())->get();

        $this->addTemplateVariable('characters', $characters);

        return $this->render('Dash/index.html.twig');
    }
}
