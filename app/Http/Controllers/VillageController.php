<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVillageRequest;
use App\Http\Requests\UpdateVillageRequest;
use App\Models\District;
use App\Models\Village;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VillageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Village::query()->with('district')->orderBy('sort_order')->orderBy('name'))
                ->addColumn('district_name', fn (Village $village) => $village->district?->name ?? '-')
                ->addColumn('status_label', fn (Village $village) => $village->is_active ? 'Aktif' : 'Tidak aktif')
                ->addColumn('action', fn (Village $village) => TableActions::editDelete(
                    route('villages.edit', $village),
                    route('villages.destroy', $village),
                    'Hapus kampung ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('villages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('villages.form', ['village' => new Village, 'districts' => District::query()->orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVillageRequest $request): RedirectResponse
    {
        Village::create($request->validated());

        return redirect()->route('villages.index')->with('status', 'Kampung berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Village $village): View
    {
        return view('villages.form', ['village' => $village, 'districts' => District::query()->orderBy('name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVillageRequest $request, Village $village): RedirectResponse
    {
        $village->update($request->validated());

        return redirect()->route('villages.index')->with('status', 'Kampung berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Village $village): RedirectResponse
    {
        abort_if($village->reports()->exists(), 422, 'Kampung masih memiliki data laporan.');

        $village->delete();

        return redirect()->route('villages.index')->with('status', 'Kampung berhasil dihapus.');
    }
}
