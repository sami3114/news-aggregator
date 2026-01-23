<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface UserPreferenceRepositoryInterface
{
    /**
     * Find user preferences by user id
     *
     * @param int $userId
     * @return mixed
     */
    public function findByUserId(int $userId);

    /**
     * Update or create user preferences
     *
     * @param int $userId
     * @param array $data
     * @return mixed
     */
    public function updateOrCreatePreferences(int $userId, array $data);
    /**
     * Get articles by user preferences
     *
     * @param array $preferences
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getByPreferences(array $preferences, ?int $perPage = null): LengthAwarePaginator;
}
