<?php

namespace App\Models\UserPreference;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Relationships
{
    /**
     * Get the user that owns the UserPreference.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
