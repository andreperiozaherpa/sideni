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
}
