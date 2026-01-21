<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreferenceRequest;
use App\Http\Resources\ArticleCollection;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function __construct(protected ArticleRepositoryInterface $articleRepository)
    {
        //
    }

    /**
     * Get user preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $preference = UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]
        );

        return response()->json([
            'data' => [
                'preferred_sources' => $preference->preferred_sources ?? [],
                'preferred_categories' => $preference->preferred_categories ?? [],
                'preferred_authors' => $preference->preferred_authors ?? [],
            ],
        ]);
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

        $preference = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_sources' => $validated['preferred_sources'] ?? [],
                'preferred_categories' => $validated['preferred_categories'] ?? [],
                'preferred_authors' => $validated['preferred_authors'] ?? [],
            ]
        );

        return response()->json([
            'message' => 'Preferences updated successfully',
            'data' => [
                'preferred_sources' => $preference->preferred_sources,
                'preferred_categories' => $preference->preferred_categories,
                'preferred_authors' => $preference->preferred_authors,
            ],
        ]);
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
        $perPage = $request->input('per_page', 15);

        $preference = UserPreference::where('user_id', $user->id)->first();

        if (!$preference) {
            // Return general feed if no preferences set
            $articles = $this->articleRepository->getAll([], $perPage);
            return new ArticleCollection($articles);
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
            return new ArticleCollection($articles);
        }

        $articles = $this->articleRepository->getByPreferences($preferences, $perPage);

        return new ArticleCollection($articles);
    }
}
