<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StoreSportModeRequest;
use App\Http\Requests\Catalog\UpdateSportModeRequest;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\FormationResource;
use App\Http\Resources\Catalog\PositionResource;
use App\Http\Resources\Catalog\SportModeResource;
use App\Models\SportMode;
use App\Services\Catalog\CategoryService;
use App\Services\Catalog\FormationService;
use App\Services\Catalog\PositionService;
use App\Services\Catalog\SportModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SportModeController extends Controller
{
    public function __construct(
        private readonly SportModeService $sportModeService,
        private readonly CategoryService $categoryService,
        private readonly FormationService $formationService,
        private readonly PositionService $positionService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/sport-modes/Index', [
            'sportModes' => SportModeResource::collection($this->sportModeService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/sport-modes/Create', [
            'categories' => CategoryResource::collection($this->categoryService->listAll()),
            'formations' => FormationResource::collection($this->formationService->listAll()),
            'positions' => PositionResource::collection($this->positionService->listAll()),
        ]);
    }

    public function store(StoreSportModeRequest $request): RedirectResponse
    {
        $sportMode = $this->sportModeService->create($request->safe()->only([
            'key',
            'label_key',
            'description_key',
            'icon',
        ]));

        $this->sportModeService->syncCategories($sportMode, $request->validated('category_ids', []));
        $this->sportModeService->syncFormations($sportMode, $request->validated('formation_ids', []));
        $this->sportModeService->syncPositions($sportMode, $request->validated('position_ids', []));

        return to_route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade criada com sucesso.');
    }

    public function edit(Request $request, SportMode $sportMode): Response
    {
        $this->ensureAdmin($request);
        $sportMode->load(['categories', 'formations', 'positions']);

        return Inertia::render('admin/catalog/sport-modes/Edit', [
            'sportMode' => new SportModeResource($sportMode),
            'categories' => CategoryResource::collection($this->categoryService->listAll()),
            'formations' => FormationResource::collection($this->formationService->listAll()),
            'positions' => PositionResource::collection($this->positionService->listAll()),
        ]);
    }

    public function update(UpdateSportModeRequest $request, SportMode $sportMode): RedirectResponse
    {
        $this->sportModeService->update($sportMode, $request->safe()->only([
            'key',
            'label_key',
            'description_key',
            'icon',
        ]));

        $this->sportModeService->syncCategories($sportMode, $request->validated('category_ids', []));
        $this->sportModeService->syncFormations($sportMode, $request->validated('formation_ids', []));
        $this->sportModeService->syncPositions($sportMode, $request->validated('position_ids', []));

        return to_route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade atualizada com sucesso.');
    }

    public function destroy(Request $request, SportMode $sportMode): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->sportModeService->delete($sportMode);

        return to_route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade removida com sucesso.');
    }
}
