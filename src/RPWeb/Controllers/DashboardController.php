<?php

namespace RPWeb\Controllers;

use RPWeb\Library\Controller;

class DashboardController extends Controller
{
    /**
     * Index page.
     *
     * @return void
     */
    public function index() {
        return $this->render('Dash/index.html.twig');
    }
}
