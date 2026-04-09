<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StorePositionRequest;
use App\Http\Requests\Catalog\UpdatePositionRequest;
use App\Http\Resources\Catalog\PositionResource;
use App\Models\Position;
use App\Services\Catalog\PositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(
        private readonly PositionService $positionService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/positions/Index', [
            'positions' => PositionResource::collection($this->positionService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/positions/Create');
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $this->positionService->create($request->validated());

        return to_route('admin.catalog.positions.index')
            ->with('success', 'Posição criada com sucesso.');
    }

    public function edit(Request $request, Position $position): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/positions/Edit', [
            'position' => new PositionResource($position),
        ]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $this->positionService->update($position, $request->validated());

        return to_route('admin.catalog.positions.index')
            ->with('success', 'Posição atualizada com sucesso.');
    }

    public function destroy(Request $request, Position $position): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->positionService->delete($position);

        return to_route('admin.catalog.positions.index')
            ->with('success', 'Posição removida com sucesso.');
    }
}
