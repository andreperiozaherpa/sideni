<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportCategoryRequest;
use App\Http\Requests\UpdateReportCategoryRequest;
use App\Models\ReportCategory;
use App\Support\TableActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReportCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(ReportCategory::query()->orderBy('sort_order'))
                ->addColumn('status_label', fn (ReportCategory $category) => $category->is_active ? 'Aktif' : 'Tidak aktif')
                ->addColumn('action', fn (ReportCategory $category) => TableActions::editDelete(
                    route('report-categories.edit', $category),
                    route('report-categories.destroy', $category),
                    'Hapus kategori ini?',
                ))
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('report-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('report-categories.form', ['category' => new ReportCategory]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportCategoryRequest $request): RedirectResponse
    {
        ReportCategory::create($request->validated());

        return redirect()->route('report-categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(ReportCategory $reportCategory): View
    {
        return view('report-categories.form', ['category' => $reportCategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportCategoryRequest $request, ReportCategory $reportCategory): RedirectResponse
    {
        $reportCategory->update($request->validated());

        return redirect()->route('report-categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportCategory $reportCategory): RedirectResponse
    {
        abort_if(
            $reportCategory->reports()->exists() || $reportCategory->incidentTypes()->exists(),
            422,
            'Kategori masih digunakan oleh laporan atau jenis kejadian.',
        );

        $reportCategory->delete();

        return redirect()->route('report-categories.index')->with('status', 'Kategori berhasil dihapus.');
    }
}
