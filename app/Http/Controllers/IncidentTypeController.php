<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentTypeRequest;
use App\Http\Requests\UpdateIncidentTypeRequest;
use App\Models\IncidentType;
use App\Models\ReportCategory;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class IncidentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(IncidentType::query()->with('reportCategory')->orderBy('sort_order'))
                ->addColumn('category_name', fn (IncidentType $incidentType) => $incidentType->reportCategory?->name ?? '-')
                ->addColumn('status_label', fn (IncidentType $incidentType) => $incidentType->is_active ? 'Aktif' : 'Tidak aktif')
                ->addColumn('action', fn (IncidentType $incidentType) => TableActions::editDelete(
                    route('incident-types.edit', $incidentType),
                    route('incident-types.destroy', $incidentType),
                    'Hapus jenis kejadian ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('incident-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('incident-types.form', ['incidentType' => new IncidentType, 'categories' => ReportCategory::query()->orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIncidentTypeRequest $request): RedirectResponse
    {
        IncidentType::create($request->validated());

        return redirect()->route('incident-types.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(IncidentType $incidentType): View
    {
        return view('incident-types.form', ['incidentType' => $incidentType, 'categories' => ReportCategory::query()->orderBy('name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncidentTypeRequest $request, IncidentType $incidentType): RedirectResponse
    {
        $incidentType->update($request->validated());

        return redirect()->route('incident-types.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncidentType $incidentType): RedirectResponse
    {
        if ($incidentType->reports()->exists()) {
            return redirect()->route('incident-types.index')->withErrors([
                'incident_type' => 'Jenis kejadian "'.$incidentType->name.'" masih digunakan oleh laporan.',
            ]);
        }

        $incidentType->delete();

        return redirect()->route('incident-types.index')->with('status', 'Jenis kejadian berhasil dihapus.');
    }
}
