<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDistrictRequest;
use App\Http\Requests\UpdateDistrictRequest;
use App\Models\District;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(District::query()->withCount('villages')->orderBy('sort_order')->orderBy('name'))
                ->addColumn('status_label', fn (District $district) => $district->is_active ? 'Aktif' : 'Tidak aktif')
                ->addColumn('action', fn (District $district) => TableActions::editDelete(
                    route('districts.edit', $district),
                    route('districts.destroy', $district),
                    'Hapus kecamatan ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('districts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('districts.form', ['district' => new District]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDistrictRequest $request): RedirectResponse
    {
        District::create($request->validated());

        return redirect()->route('districts.index')->with('status', 'Kecamatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(District $district): View
    {
        return view('districts.form', ['district' => $district]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDistrictRequest $request, District $district): RedirectResponse
    {
        $district->update($request->validated());

        return redirect()->route('districts.index')->with('status', 'Kecamatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(District $district): RedirectResponse
    {
        abort_if($district->villages()->exists(), 422, 'Kecamatan masih memiliki data kampung.');

        $district->delete();

        return redirect()->route('districts.index')->with('status', 'Kecamatan berhasil dihapus.');
    }
}
