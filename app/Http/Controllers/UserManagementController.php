<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\District;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(User::query()->with('roles', 'village', 'district')->orderByDesc('id'))
                ->filterColumn('village_name', fn ($query, $keyword) => $query->whereHas('village', fn ($query) => $query->where('name', 'like', "%{$keyword}%")))
                ->filterColumn('district_name', fn ($query, $keyword) => $query->whereHas('district', fn ($query) => $query->where('name', 'like', "%{$keyword}%")))
                ->addColumn('district_name', fn (User $user) => $user->district?->name ?? '-')
                ->addColumn('village_name', fn (User $user) => $user->village?->name ?? '-')
                ->addColumn('roles_label', fn (User $user) => $user->getRoleNames()->join(', ') ?: '-')
                ->addColumn('action', fn (User $user) => $this->userActions($user))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.form', ['user' => new User, 'roles' => Role::query()->orderBy('name')->get(), 'villages' => $this->villages(), 'districts' => $this->districts()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->only(['name', 'username', 'email', 'password', 'village_id', 'district_id']));
        $user->syncRoles(Role::query()->whereKey($request->input('roles'))->get());

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user->load('roles'), 'roles' => Role::query()->orderBy('name')->get(), 'villages' => $this->villages(), 'districts' => $this->districts()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->safe()->only(['name', 'username', 'email', 'village_id', 'district_id']);

        if ($request->filled('password')) {
            $attributes['password'] = $request->string('password')->toString();
        }

        $user->update($attributes);
        $user->syncRoles(Role::query()->whereKey($request->input('roles'))->get());

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('users.index')->withErrors([
                'user' => 'Akun yang sedang Anda gunakan tidak dapat dihapus.',
            ]);
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return redirect()->route('users.index')->withErrors([
                'user' => 'Super Admin terakhir tidak dapat dihapus.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil dihapus.');
    }

    private function userActions(User $user): string
    {
        return '<div class="flex gap-3">'
            .'<a class="font-semibold text-sky-700" href="'.route('users.edit', $user).'">Ubah</a>'
            .'<form method="POST" action="'.route('users.destroy', $user).'" data-confirm="Hapus pengguna ini?">'
            .csrf_field().method_field('DELETE')
            .'<button class="font-semibold text-red-700" type="submit">Hapus</button></form>'
            .'</div>';
    }

    /**
     * Villages offered when assigning a user to a village.
     *
     * @return Collection<int, Village>
     */
    private function villages(): Collection
    {
        return Village::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Districts offered when assigning a user to a district.
     *
     * @return Collection<int, District>
     */
    private function districts(): Collection
    {
        return District::query()->orderBy('name')->get();
    }
}
