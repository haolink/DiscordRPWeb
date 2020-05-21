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
        if(!is_null($this->getUser())) {
            return redirect(url('dash.index'));
        }
        
        return $this->render('Index/index.html.twig');
    }
}
