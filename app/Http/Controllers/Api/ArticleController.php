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
    public function __construct(
        protected ArticleRepositoryInterface $articleRepository
    ) {
        //
    }

    /**
     * Get all articles with optional filters
     */
    public function index(ArticleFilterRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->input('per_page', config('pagination.per_page'));

        $articles = $this->articleRepository->getAll($filters, $perPage);
        $articleCollection = new ArticleCollection($articles);

        return ResponseService::successResponse('Articles retrieved successfully', $articleCollection);
    }

    /**
     * Get a single article
     */
    public function show(Article $article): JsonResponse
    {
        $article->load(['author', 'categories']);
        $articleResource = new ArticleResource($article);

        return ResponseService::successResponse('Article retrieved successfully', $articleResource);
    }

    /**
     * Search articles
     */
    public function search(ArticleFilterRequest $request): JsonResponse
    {
        $query = $request->input('q');
        $filters = $request->validated();
        $perPage = $request->input('per_page', config('pagination.per_page'));

        $articles = $this->articleRepository->search($query, $filters, $perPage);
        $articleCollection = new ArticleCollection($articles);

        return ResponseService::successResponse('Articles retrieved successfully', $articleCollection);
    }

    /**
     * Get all categories
     */
    public function categories(): JsonResponse
    {
        $categories = $this->articleRepository->getCategories();

        return ResponseService::successResponse('Categories retrieved successfully', $categories);
    }

    /**
     * Get all sources
     */
    public function sources(): JsonResponse
    {
        $sources = $this->articleRepository->getSources();

        return ResponseService::successResponse('Sources retrieved successfully', $sources);
    }

    /**
     * Get all authors
     */
    public function authors(): JsonResponse
    {
        $authors = $this->articleRepository->getAuthors();

        return ResponseService::successResponse('Authors retrieved successfully', $authors);
    }
}
