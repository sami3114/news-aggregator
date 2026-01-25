<?php

namespace App\Models\User;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Relationships
{
    /**
     * Get the user's preference.
     *
     * @return HasOne
     */
    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }
}
