<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Role::query()->withCount(['permissions', 'users'])->orderBy('name'))
                ->addColumn('action', fn (Role $role) => TableActions::editDelete(
                    route('roles.edit', $role),
                    route('roles.destroy', $role),
                    'Hapus role ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('roles.index');
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        return view('roles.form', ['role' => new Role, 'permissions' => Permission::query()->orderBy('name')->get()]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->string('name')->toString(), 'guard_name' => 'web']);
        $role->syncPermissions($this->permissionsFrom($request->input('permissions', [])));

        return redirect()->route('roles.index')->with('status', 'Role berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        return view('roles.form', ['role' => $role->load('permissions'), 'permissions' => Permission::query()->orderBy('name')->get()]);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->string('name')->toString()]);
        $role->syncPermissions($this->permissionsFrom($request->input('permissions', [])));

        return redirect()->route('roles.index')->with('status', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified role when no user still holds it.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $usersCount = $role->users()->count();

        if ($usersCount > 0) {
            return redirect()->route('roles.index')->withErrors([
                'role' => 'Role "'.$role->name.'" masih digunakan oleh '.$usersCount.' pengguna. Lepaskan role ini dari pengguna tersebut sebelum menghapusnya.',
            ]);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role berhasil dihapus.');
    }

    /**
     * @param  array<int, int|string>  $permissionIds
     * @return Collection<int, Permission>
     */
    private function permissionsFrom(array $permissionIds): Collection
    {
        return Permission::query()->whereKey($permissionIds)->get();
    }
}
