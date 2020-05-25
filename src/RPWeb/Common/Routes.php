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

        SimpleRouter::get('/help/user', 'HelpController@help', array('parameters' => ['helpPage' => 'user']))->name('help.user');
        SimpleRouter::get('/help/server', 'HelpController@help', array('parameters' => ['helpPage' => 'guild']))->name('help.guild');
        SimpleRouter::get('/help/faq', 'HelpController@help', array('parameters' => ['helpPage' => 'faq']))->name('help.faq');
        
        SimpleRouter::group(['middleware' => DiscordAuthMiddleware::class], function () {
            SimpleRouter::get('/characters', 'CharacterController@index')->name('character.index');

            SimpleRouter::post('/characters/submit', 'CharacterController@submit')->name('character.submit');
            SimpleRouter::get('/characters/new', 'CharacterController@new')->name('character.new');            
            SimpleRouter::get('/characters/delete/{id}', 'CharacterController@delete')->name('character.delete');
            SimpleRouter::get('/characters/{id}', 'CharacterController@edit')->name('character.edit');
        });
    }
}
