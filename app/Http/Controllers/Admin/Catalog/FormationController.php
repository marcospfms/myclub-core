<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StoreFormationRequest;
use App\Http\Requests\Catalog\UpdateFormationRequest;
use App\Http\Resources\Catalog\FormationResource;
use App\Models\Formation;
use App\Services\Catalog\FormationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormationController extends Controller
{
    public function __construct(
        private readonly FormationService $formationService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/formations/Index', [
            'formations' => FormationResource::collection($this->formationService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/formations/Create');
    }

    public function store(StoreFormationRequest $request): RedirectResponse
    {
        $this->formationService->create($request->validated());

        return to_route('admin.catalog.formations.index')
            ->with('success', 'Formação criada com sucesso.');
    }

    public function edit(Request $request, Formation $formation): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/formations/Edit', [
            'formation' => new FormationResource($formation),
        ]);
    }

    public function update(UpdateFormationRequest $request, Formation $formation): RedirectResponse
    {
        $this->formationService->update($formation, $request->validated());

        return to_route('admin.catalog.formations.index')
            ->with('success', 'Formação atualizada com sucesso.');
    }

    public function destroy(Request $request, Formation $formation): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->formationService->delete($formation);

        return to_route('admin.catalog.formations.index')
            ->with('success', 'Formação removida com sucesso.');
    }
}
