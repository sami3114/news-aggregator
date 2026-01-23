<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\UserPreferenceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreferenceRequest;
use App\Http\Resources\ArticleCollection;
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
     * Get user preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $preference = $this->userPreferenceRepository->findByUserId($user->id);

        if (!$preference) {
            return ResponseService::successResponse(
                'No preferences found',
                [
                    'preferred_sources' => [],
                    'preferred_categories' => [],
                    'preferred_authors' => [],
                ]
            );
        }

        return ResponseService::successResponse(
            'Preferences retrieved successfully',
            [
                'preferred_sources' => $preference->preferred_sources ?? [],
                'preferred_categories' => $preference->preferred_categories ?? [],
                'preferred_authors' => $preference->preferred_authors ?? [],
            ]
        );
    }

    /**
     * Update user preferences
     *
     * @param UserPreferenceRequest $request
     * @return JsonResponse
     */
    public function update(UserPreferenceRequest $request): JsonResponse
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

    /**
     * Get personalized feed based on user preferences
     *
     * @param Request $request
     * @return ArticleCollection|JsonResponse
     */
    public function feed(Request $request): ArticleCollection|JsonResponse
    {

        $user = $request->user();
        $perPage = $request->input('per_page', config('pagination.per_page'));

        $preference = $this->userPreferenceRepository->findByUserId($user->id);

        if (!$preference) {
            $articles = $this->articleRepository->getAll([], $perPage);
            $articleCollection = new ArticleCollection($articles);

            return ResponseService::successResponse('Personalized feed retrieved successfully', $articleCollection);
        }

        $preferences = [
            'preferred_sources' => $preference->preferred_sources ?? [],
            'preferred_categories' => $preference->preferred_categories ?? [],
            'preferred_authors' => $preference->preferred_authors ?? [],
        ];

        // If all preferences are empty, return general feed
        if (empty($preferences['preferred_sources']) &&
            empty($preferences['preferred_categories']) &&
            empty($preferences['preferred_authors'])) {
            $articles = $this->articleRepository->getAll([], $perPage);
            $articleCollection = new ArticleCollection($articles);

            return ResponseService::successResponse('Personalized feed retrieved successfully', $articleCollection);
        }

        $articles = $this->userPreferenceRepository->getByPreferences($preferences, $perPage);
        $articleCollection = new ArticleCollection($articles);

        return ResponseService::successResponse('Personalized feed retrieved successfully', $articleCollection);
    }
}
