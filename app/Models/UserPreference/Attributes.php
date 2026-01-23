<?php

namespace App\Models\UserPreference;

trait Attributes
{
    /**
     * Scope for filtering by user.
     *
     * @param $query
     * @param int $userId
     * @return mixed
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
