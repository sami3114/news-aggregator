<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\UserPreferenceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreferenceRequest;
use App\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function __construct(
        protected UserPreferenceRepositoryInterface $userPreferenceRepository,
        protected ArticleRepositoryInterface $articleRepository
    ){}

    /**
     * Update user preferences
     *
     * @param UserPreferenceRequest $request
     * @return JsonResponse
     */
    public function __invoke(UserPreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $preference = $this->userPreferenceRepository->updateOrCreatePreferences($user->id, $validated);

        return ResponseService::successResponse(
            'Preferences updated successfully',
            [
                'preferred_sources' => $preference->preferred_sources,
                'preferred_categories' => $preference->preferred_categories,
                'preferred_authors' => $preference->preferred_authors,
            ]
        );
    }
}
