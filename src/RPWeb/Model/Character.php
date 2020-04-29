<?php

namespace RPWeb\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Character extends Model
{
    protected $table = 'characters';

    protected $fillable = array('user_id', 'character_shortname', 'character_name', 'character_avatar', 'default_character');

    /**
     * User relation.
     *
     * @return BelongsTo
     */
    public function user() 
    {
        return $this->belongsTo(Character::class, 'user_id', 'id');
    }
}
