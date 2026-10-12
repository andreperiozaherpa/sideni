<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Support\ReportBadges;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReportVerificationController extends Controller
{
    /**
     * List reports that are waiting for verification.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Report::query()->where('status', 'pending')->with('village', 'reportCategory');

        if ($request->ajax()) {
            return DataTables::eloquent($query->orderBy('status_changed_at')->orderBy('id'))
                ->addColumn('village_name', fn (Report $report) => $report->village?->name ?? '-')
                ->addColumn('category_name', fn (Report $report) => $report->reportCategory?->name ?? '-')
                ->addColumn('risk_label', fn (Report $report) => str($report->risk_level->value)->title())
                ->addColumn('submitted_label', fn (Report $report) => $report->created_at?->format('d/m/Y H:i'))
                ->addColumn('sla', fn (Report $report) => ReportBadges::sla($report))
                ->addColumn('action', fn (Report $report) => '<a class="link" href="'.route('reports.verify.show', $report).'">Verifikasi</a>')
                ->rawColumns(['sla', 'action'])
                ->toJson();
        }

        return view('reports.verify-index');
    }

    /**
     * Show the verification screen for a single report.
     */
    public function show(Report $report): View
    {
        return view('reports.verify', [
            'report' => $report->load('village.district', 'reportCategory', 'incidentType', 'submittedBy', 'attachments', 'statusHistories.changedBy'),
        ]);
    }
}
