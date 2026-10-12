<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the analytical dashboard for the authenticated user.
     */
    public function index(): View
    {
        $user = auth()->user();

        $statusCounts = Report::query()->visibleTo($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $riskCounts = Report::query()->visibleTo($user)
            ->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        $categoryCounts = Report::query()->visibleTo($user)
            ->join('report_categories', 'report_categories.id', '=', 'reports.report_category_id')
            ->selectRaw('report_categories.name as name, count(*) as total')
            ->groupBy('report_categories.name')
            ->pluck('total', 'name');

        $months = collect(range(11, 0))->map(fn (int $offset) => now()->subMonths($offset)->format('Y-m'));
        $monthly = Report::query()->visibleTo($user)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn (Report $report) => $report->created_at->format('Y-m'))
            ->map->count();

        $statusLabels = collect(ReportStatus::cases())->mapWithKeys(
            fn (ReportStatus $status) => [$status->value => str($status->value)->replace('_', ' ')->title()->toString()],
        );
        $riskLabels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];

        $mapPoints = Report::query()->visibleTo($user)->with('village')->latest()->limit(500)->get()->map(
            fn (Report $report) => [
                'number' => $report->registration_number,
                'lat' => (float) $report->latitude,
                'lng' => (float) $report->longitude,
                'status' => $report->status->value,
                'risk' => $report->risk_level->value,
                'village' => $report->village?->name,
                'location' => $report->location_name,
            ],
        );

        return view('dashboard', [
            'stats' => [
                'total' => Report::query()->visibleTo($user)->count(),
                'pending' => (int) ($statusCounts[ReportStatus::Pending->value] ?? 0),
                'inProgress' => (int) ($statusCounts[ReportStatus::InProgress->value] ?? 0),
                'completed' => (int) ($statusCounts[ReportStatus::Completed->value] ?? 0),
            ],
            'statusChart' => [
                'labels' => $statusLabels->values(),
                'values' => $statusLabels->keys()->map(fn (string $value) => (int) ($statusCounts[$value] ?? 0))->values(),
            ],
            'riskChart' => [
                'labels' => array_values($riskLabels),
                'values' => collect($riskLabels)->keys()->map(fn (string $value) => (int) ($riskCounts[$value] ?? 0))->values(),
            ],
            'categoryChart' => [
                'labels' => $categoryCounts->keys()->values(),
                'values' => $categoryCounts->values(),
            ],
            'monthlyChart' => [
                'labels' => $months->map(fn (string $month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->values(),
                'values' => $months->map(fn (string $month) => (int) ($monthly[$month] ?? 0))->values(),
            ],
            'mapPoints' => $mapPoints,
            'recentReports' => Report::query()->visibleTo($user)->with('village', 'reportCategory')->latest()->limit(5)->get(),
        ]);
    }
}
