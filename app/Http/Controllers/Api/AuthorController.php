<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthorRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;

class AuthorController extends Controller
{
    public function __construct(protected AuthorRepositoryInterface $authorRepository)
    {

    }


    /**
     * Get all authors
     */
    public function authors(): JsonResponse
    {
        $authors = $this->authorRepository->getAuthors();

        return ResponseService::successResponse('Authors retrieved successfully', $authors);
    }
}
