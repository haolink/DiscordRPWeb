<?php

namespace RPWeb\Common;

use Pecee\SimpleRouter\SimpleRouter;
use RPWeb\Middleware\DiscordAuthMiddleware;

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
        SimpleRouter::get('/auth', 'AuthController@auth')->name('auth.auth');
        SimpleRouter::get('/auth/scope', 'AuthController@scope')->name('auth.scope');
        SimpleRouter::get('/logout', 'AuthController@logout')->name('auth.logout');

        SimpleRouter::group(['middleware' => DiscordAuthMiddleware::class], function () {
            SimpleRouter::get('/dash', 'DashboardController@index')->name('dash.index');
        });
    }
}
