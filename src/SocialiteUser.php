<?php

namespace CreatvStudio\SocialiteAuth;

use Illuminate\Database\Eloquent\Model;

class SocialiteUser extends Model
{
    protected $table = 'socialite_users';

    protected $guarded = [];

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function socialiteable()
    {
        return $this->morphTo('socialiteable');
    }

    public function scopeProvider($query, $provider)
    {
        $query->where('provider', $provider);
    }
}
