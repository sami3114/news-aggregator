<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function __construct(protected ArticleRepositoryInterface $articleRepository)
    {
        //
    }

    /**
     * Get all articles with optional filters
     *
     * @param ArticleFilterRequest $request
     * @return ArticleCollection
     */
    public function index(ArticleFilterRequest $request): ArticleCollection
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', 15);

        $articles = $this->articleRepository->getAll($filters, $perPage);

        return new ArticleCollection($articles);
    }

    /**
     * Get a single article
     *
     * @param int $id
     * @return ArticleResource|JsonResponse
     */
    public function show(int $id): ArticleResource|JsonResponse
    {
        try {
            $article = $this->articleRepository->findById($id);
            return new ArticleResource($article);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Article not found',
            ], 404);
        }
    }

    /**
     * Search articles
     *
     * @param ArticleFilterRequest $request
     * @return ArticleCollection|JsonResponse
     */
    public function search(ArticleFilterRequest $request): ArticleCollection|JsonResponse
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([
                'message' => 'Search query is required',
            ], 422);
        }

        $filters = $request->validated();
        $perPage = $request->input('per_page', 15);

        $articles = $this->articleRepository->search($query, $filters, $perPage);

        return new ArticleCollection($articles);
    }

    /**
     * Get all categories
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->articleRepository->getCategories();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get all sources
     *
     * @return JsonResponse
     */
    public function sources(): JsonResponse
    {
        $sources = $this->articleRepository->getSources();

        return response()->json([
            'data' => $sources,
        ]);
    }

    /**
     * Get all authors
     *
     * @return JsonResponse
     */
    public function authors(): JsonResponse
    {
        $authors = $this->articleRepository->getAuthors();

        return response()->json([
            'data' => $authors,
        ]);
    }
}
