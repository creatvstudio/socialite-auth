<?php

namespace CreatvStudio\SocialiteAuth;

use CreatvStudio\SocialiteAuth\SocialiteUser;

trait HasSocialite
{
    /**
     * Get the socialite user that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function socialites()
    {
        return $this->morphMany(SocialiteUser::class, 'socialiteable');
    }
}
