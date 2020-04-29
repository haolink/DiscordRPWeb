<?php

namespace RPWeb\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = array('id', 'ooc_prefix');

    /**
     * Characters of the current user.
     *
     * @return HasMany
     */
    public function characters() : HasMany
    {
        return $this->hasMany(Character::class, 'user_id', 'id');
    }
}
