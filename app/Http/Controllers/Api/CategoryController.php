<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository)
    {
        //
    }

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->getCategories();

        return ResponseService::successResponse('Categories retrieved successfully', $categories);
    }
}
