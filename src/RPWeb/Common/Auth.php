<?php

namespace RPWeb\Common;

use RPWeb\Common\DiscordUser;

class Auth
{
    /**
     * User object.
     * 
     * @return DiscordUser
     */
    private static $user = null;

    /**
     * Determines the currently logged in user id.
     *
     * @return DiscordUser|null
     */
    public static function getUser() : ?DiscordUser
    {
        if (!is_null(self::$user)) {
            return self::$user;
        }

        global $_SESSION;

        if (!array_key_exists('userdata', $_SESSION)) {
            return null;
        }

        $user = DiscordUser::unserialize($_SESSION['userdata']);

        if (!is_null($user)) {
            self::$user = $user;
            return $user;
        }

        return null;
    }

    /**
     * Logs in a user.
     *
     * @param DiscordUser $user
     * @return void
     */
    public static function login(DiscordUser $user) 
    {
        global $_SESSION;

        self::$user = $user;

        $_SESSION['userdata'] = $user->serialize();
    }

    /**
     * Logs out.
     *
     * @return void
     */
    public static function logout() 
    {
        global $_SESSION;

        self::$user = null;

        if (array_key_exists('userdata', $_SESSION)) {
            unset($_SESSION['userdata']);
        }        
    }
}
