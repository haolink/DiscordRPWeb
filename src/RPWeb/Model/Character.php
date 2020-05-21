<?php

namespace RPWeb\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RPWeb\Common\Kernel;

class Character extends Model
{
    private static $AVATAR_URL_PREFIX = null;

    protected $table = 'characters';

    protected $fillable = array('user_id', 'character_shortname', 'character_name', 'character_avatar', 'default_character');

    public $timestamps = false;

    /**
     * User relation.
     *
     * @return BelongsTo
     */
    public function user() 
    {
        return $this->belongsTo(Character::class, 'user_id', 'id');
    }

    /**
     * Gets a resolved avatar URL.
     *
     * @return void
     */
    public function getResolvedAvatarUrl() 
    {
        $avatar = $this->character_avatar;

        if (strpos($avatar, '://') === false) {            
            $prefix = self::$AVATAR_URL_PREFIX;
            if(is_null($prefix)) {
                $config = Kernel::getInstance()->getConfig();
                $prefix = $config['avatar']['storage_url'];
                self::$AVATAR_URL_PREFIX = $prefix;
            }            
            $avatar = $prefix . $avatar;
        }

        return $avatar;
    }
}
