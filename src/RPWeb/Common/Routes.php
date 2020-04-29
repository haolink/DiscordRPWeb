<?php

namespace RPWeb\Common;

use Pecee\SimpleRouter\SimpleRouter;

class Routes
{
    /**
     * Routes have already been initialised?
     *
     * @var boolean
     */
    private static $initialised = false;

    /**
     * Initalises the system routes.
     *
     * @return void
     */
    public static function initRoutes() {
        if (self::$initialised) {
            return;
        }

        self::$initialised = true;

        SimpleRouter::get('/', 'IndexController@index')->name('index');
    }
}
