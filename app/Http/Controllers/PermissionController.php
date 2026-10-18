<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Permission::query()->withCount('roles')->orderBy('name'))
                ->addColumn('action', fn (Permission $permission) => TableActions::editDelete(
                    route('permissions.edit', $permission),
                    route('permissions.destroy', $permission),
                    'Hapus hak akses ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('permissions.index');
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): View
    {
        return view('permissions.form', ['permission' => new Permission]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create(['name' => $request->string('name')->toString(), 'guard_name' => 'web']);

        return redirect()->route('permissions.index')->with('status', 'Hak akses berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission): View
    {
        return view('permissions.form', ['permission' => $permission]);
    }

    /**
     * Update the specified permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update(['name' => $request->string('name')->toString()]);

        return redirect()->route('permissions.index')->with('status', 'Hak akses berhasil diperbarui.');
    }

    /**
     * Remove the specified permission when no role still uses it.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')->withErrors([
                'permission' => 'Hak akses "'.$permission->name.'" masih digunakan oleh role.',
            ]);
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('status', 'Hak akses berhasil dihapus.');
    }
}
