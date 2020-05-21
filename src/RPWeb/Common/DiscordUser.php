<?php

namespace RPWeb\Common;

class DiscordUser
{
    /**
     * User ID.
     *
     * @var string
     */    
    private $id;

    /**
     * Username.
     *
     * @var string
     */    
    private $username;

    /**
     * User 4 digits.
     *
     * @var string
     */    
    private $discriminator;

    /**
     * User avatar.
     *
     * @var string
     */    
    private $avatar;

    /**
     * Makes the constructor private.
     */
    private function __construct()
    { }

    /**
     * Serialise for session.
     *
     * @return string
     */
    public function serialize() : string
    {
        return json_encode(array(
            'id' => $this->id,
            'username' => $this->username,
            'discriminator' => $this->discriminator,
            'avatar' => $this->avatar
        ));
    }

    /**
     * Unserialises a user from the session.
     *
     * @param array $input
     * @return DiscordUser|null
     */
    public static function unserialize(string $input) : ?DiscordUser
    {
        $arr = null;
        try {
            $arr = @json_decode($input, true);
        } catch(\Exception $ex) {
            $arr = null;
        }
        if (is_null($arr)) {
            return null;
        }

        foreach (array('id', 'username', 'discriminator', 'avatar') as $key) {
            if (!array_key_exists($key, $arr) || is_null($arr[$key])) {
                return null;
            }
        }

        $res = new DiscordUser();
        $res->id = $arr['id'];
        $res->username = $arr['username'];
        $res->discriminator = $arr['discriminator'];
        $res->avatar = $arr['avatar'];

        return $res;
    }

    /**
     * Unserialises a user from Discord API data.
     *
     * @param array $input
     * @return DiscordUser|null
     */
    public static function unserializeFromDiscord(array $input) : ?DiscordUser
    {
        foreach (array('id', 'username', 'discriminator', 'avatar') as $key) {
            if (!array_key_exists($key, $input) || is_null($input[$key])) {
                return null;
            }
        }

        $res = new DiscordUser();
        $res->id = $input['id'];
        $res->username = $input['username'];
        $res->discriminator = $input['discriminator'];
        $res->avatar = 'https://cdn.discordapp.com/avatars/' . $input['id'] . '/' . $input['avatar'] . '.png';

        return $res;
    }

    /**
     * Gets the user id.
     * 
     * @return string
     */ 
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Gets the user name.
     * 
     * @return string
     */ 
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * Gets a user discriminator.
     * 
     * @return string
     */ 
    public function getDiscriminator() : string
    {
        return $this->discriminator;
    }

    /**
     * Gets a user avatar.
     * 
     * @return string
     */ 
    public function getAvatar() : string
    {
        return $this->avatar;
    }
}
