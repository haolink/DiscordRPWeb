<?php

namespace RPWeb\Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use RPWeb\Common\Auth;

class DiscordAuthMiddleware implements IMiddleware
{
    /**
     * Checks if the user is logged in via Discord OAuth.
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        $user = Auth::getUser();

        if($user === null) {
            $request->setRewriteUrl(url('index'));
            redirect(url('index'));
        }
    }
}
