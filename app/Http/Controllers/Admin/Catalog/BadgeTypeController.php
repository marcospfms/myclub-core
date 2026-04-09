<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StoreBadgeTypeRequest;
use App\Http\Requests\Catalog\UpdateBadgeTypeRequest;
use App\Http\Resources\Catalog\BadgeTypeResource;
use App\Models\BadgeType;
use App\Services\Catalog\BadgeTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgeTypeController extends Controller
{
    public function __construct(
        private readonly BadgeTypeService $badgeTypeService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/badge-types/Index', [
            'badgeTypes' => BadgeTypeResource::collection($this->badgeTypeService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/badge-types/Create');
    }

    public function store(StoreBadgeTypeRequest $request): RedirectResponse
    {
        $this->badgeTypeService->create($request->validated());

        return to_route('admin.catalog.badge-types.index')
            ->with('success', 'Tipo de badge criado com sucesso.');
    }

    public function edit(Request $request, BadgeType $badgeType): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/badge-types/Edit', [
            'badgeType' => new BadgeTypeResource($badgeType),
        ]);
    }

    public function update(UpdateBadgeTypeRequest $request, BadgeType $badgeType): RedirectResponse
    {
        $this->badgeTypeService->update($badgeType, $request->validated());

        return to_route('admin.catalog.badge-types.index')
            ->with('success', 'Tipo de badge atualizado com sucesso.');
    }

    public function destroy(Request $request, BadgeType $badgeType): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->badgeTypeService->delete($badgeType);

        return to_route('admin.catalog.badge-types.index')
            ->with('success', 'Tipo de badge removido com sucesso.');
    }
}
