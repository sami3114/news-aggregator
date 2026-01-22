<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\Response\ResponseService;
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
        $perPage = $request->input('per_page', config('pagination.per_page'));

        $articles = $this->articleRepository->getAll($filters, $perPage);

        return new ArticleCollection($articles);
    }

    /**
     * Get a single article
     *
     * @param Article $article
     * @return ArticleResource
     */
    public function show(Article $article): ArticleResource
    {
        // Load relationships
        $article->load(['author', 'categories']);

        return new ArticleResource($article);
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
            return ResponseService::errorResponse('Search query is required', null, 422);
        }

        $filters = $request->validated();
        $perPage = $request->input('per_page', config('pagination.per_page'));

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

        return ResponseService::successResponse('Categories retrieved successfully', $categories);
    }

    /**
     * Get all sources
     *
     * @return JsonResponse
     */
    public function sources(): JsonResponse
    {
        $sources = $this->articleRepository->getSources();

        return ResponseService::successResponse('Sources retrieved successfully', $sources);
    }

    /**
     * Get all authors
     *
     * @return JsonResponse
     */
    public function authors(): JsonResponse
    {
        $authors = $this->articleRepository->getAuthors();

        return ResponseService::successResponse('Authors retrieved successfully', $authors);
    }
}
