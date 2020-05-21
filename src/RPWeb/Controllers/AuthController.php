<?php

namespace RPWeb\Controllers;

use League\OAuth2\Client\Provider\GenericProvider;
use RPWeb\Common\Auth;
use RPWeb\Common\Kernel;
use RPWeb\Common\DiscordUser;
use RPWeb\Library\Controller;
use Wa72\Url\Url;

class AuthController extends Controller
{
    /**
     * OAuth provider.
     *
     * @var GenericProvider
     */
    private $provider;

    /**
     * Authentication controller state.
     */
    public function __construct()
    {
        $config = Kernel::getInstance()->getConfig();

        $scopeUrlRoute = url('auth.scope');
        $scopeUrl = Url::parse($scopeUrlRoute);
        
        $scopeUrl->makeAbsolute(full_url());

        $this->provider = new GenericProvider([
            'clientId'                => $config['oauth']['client_id'],
            'clientSecret'            => $config['oauth']['client_secret'],
            'redirectUri'             => $scopeUrl->__toString(),
            'urlAuthorize'            => $config['oauth']['discord_auth_url'],
            'urlAccessToken'          => $config['oauth']['discord_token_url'],
            'urlResourceOwnerDetails' => $config['oauth']['discord_provider_me']
        ]);
    }

    /**
     * Forwards for authorisation.
     *
     * @return void
     */
    public function auth()
    {
        if(!is_null($this->getUser())) {
            return redirect(url('index'));
        }

        global $_SESSION;

        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $this->provider->getAuthorizationUrl(array(
            'scope' => 'identify'
        ));

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $this->provider->getState();

        // Redirect the user to the authorization URL.
        return redirect($authorizationUrl);
    }

    /**
     * Identify user.
     * @return void 
     */
    public function scope()
    {
        if(!is_null($this->getUser())) {
            return redirect(url('dash.index'));
        }

        global $_SESSION;

        $state = input('state');
        $code = input('code');
        
        if (empty($state) || empty($code) || !isset($_SESSION['oauth2state'])) {
            return redirect(url('index'));
        }

        // Try to get an access token using the authorization code grant.
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $this->provider->getResourceOwner($accessToken);

        $userData = $resourceOwner->toArray();

        $discordId = $userData['id'] ?? null;

        if(!isset($discordId)) {
            throw new \Exception('Discord ID not found.');                
        }

        $user = DiscordUser::unserializeFromDiscord($userData);

        if (!is_null($user)) {
            Auth::login($user);
            return redirect(url('dash.index'));
        } else {
            return redirect(url('index'));
        }
    }

    /**
     * Logs out the current user.
     *
     * @return void
     */
    public function logout() 
    {
        Auth::logout();

        return redirect(url('index'));
    }
}
