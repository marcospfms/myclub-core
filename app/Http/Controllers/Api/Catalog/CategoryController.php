<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\CategoryResource;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseController
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            CategoryResource::collection($this->categoryService->listAll()),
            'Categories retrieved.'
        );
    }
}
