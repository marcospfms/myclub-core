<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Catalog\StoreStaffRoleRequest;
use App\Http\Requests\Catalog\UpdateStaffRoleRequest;
use App\Http\Resources\Catalog\StaffRoleResource;
use App\Models\StaffRole;
use App\Services\Catalog\StaffRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffRoleController extends Controller
{
    public function __construct(
        private readonly StaffRoleService $staffRoleService,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/staff-roles/Index', [
            'staffRoles' => StaffRoleResource::collection($this->staffRoleService->listAll()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/staff-roles/Create');
    }

    public function store(StoreStaffRoleRequest $request): RedirectResponse
    {
        $this->staffRoleService->create($request->validated());

        return to_route('admin.catalog.staff-roles.index')
            ->with('success', 'Função da comissão criada com sucesso.');
    }

    public function edit(Request $request, StaffRole $staffRole): Response
    {
        $this->ensureAdmin($request);

        return Inertia::render('admin/catalog/staff-roles/Edit', [
            'staffRole' => new StaffRoleResource($staffRole),
        ]);
    }

    public function update(UpdateStaffRoleRequest $request, StaffRole $staffRole): RedirectResponse
    {
        $this->staffRoleService->update($staffRole, $request->validated());

        return to_route('admin.catalog.staff-roles.index')
            ->with('success', 'Função da comissão atualizada com sucesso.');
    }

    public function destroy(Request $request, StaffRole $staffRole): RedirectResponse
    {
        $this->ensureAdmin($request);
        $this->staffRoleService->delete($staffRole);

        return to_route('admin.catalog.staff-roles.index')
            ->with('success', 'Função da comissão removida com sucesso.');
    }
}
