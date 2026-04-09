<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Http\Resources\Catalog\CategoryResource;
use App\Models\Category;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/categories/Index', [
            'categories' => CategoryResource::collection($this->categoryService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return to_route('admin.catalog.categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Request $request, Category $category): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/categories/Edit', [
            'category' => new CategoryResource($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());

        return to_route('admin.catalog.categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->categoryService->delete($category);

        return to_route('admin.catalog.categories.index')
            ->with('success', 'Categoria removida com sucesso.');
    }
}
