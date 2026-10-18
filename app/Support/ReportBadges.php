<?php

namespace App\Support;

use App\Models\Report;

class ReportBadges
{
    /**
     * Build the SLA indicator cell for a DataTables row.
     */
    public static function sla(Report $report): string
    {
        return match ($report->slaState()) {
            'overdue' => '<span class="badge bg-rose-100 text-rose-700">Terlambat</span>',
            'warning' => '<span class="badge bg-amber-100 text-amber-700">Mendekati batas</span>',
            'ok' => '<span class="badge bg-emerald-100 text-emerald-700">Aman</span>',
            default => '<span class="text-xs text-slate-400">—</span>',
        };
    }

    /**
     * Build the coloured status badge for a DataTables row.
     */
    public static function status(Report $report): string
    {
        return '<span class="badge '.self::statusClasses($report->status->value).'">'.self::statusLabel($report).'</span>';
    }

    /**
     * Human readable label for a report's status.
     */
    public static function statusLabel(Report $report): string
    {
        return str($report->status->value)->replace('_', ' ')->title()->toString();
    }

    /**
     * Colour classes used for each report status.
     */
    public static function statusClasses(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'needs_clarification' => 'bg-orange-100 text-orange-800',
            'verified' => 'bg-sky-100 text-sky-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'rejected' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    /**
     * Human readable label for a report's risk level.
     */
    public static function riskLabel(Report $report): string
    {
        return match ($report->risk_level->value) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            default => '-',
        };
    }

    /**
     * Colour classes used for each risk level.
     */
    public static function riskClasses(string $risk): string
    {
        return match ($risk) {
            'low' => 'bg-emerald-100 text-emerald-800',
            'medium' => 'bg-amber-100 text-amber-800',
            'high' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
