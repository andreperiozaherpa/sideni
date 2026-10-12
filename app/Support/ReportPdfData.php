<?php

namespace App\Support;

use App\Models\Report;
use App\Models\Setting;

class ReportPdfData
{
    /**
     * Build the shared data for every report PDF document.
     *
     * @return array<string, mixed>
     */
    public static function make(Report $report): array
    {
        $report->loadMissing('village.district', 'reportCategory', 'incidentType', 'submittedBy', 'statusHistories.changedBy');

        return [
            'report' => $report,
            'institution' => [
                'name' => Setting::get('institution_name', 'Badan Kesatuan Bangsa dan Politik'),
                'address' => Setting::get('institution_address'),
                'phone' => Setting::get('institution_phone'),
                'email' => Setting::get('institution_email'),
                'logo' => Branding::logoPath(),
                'head_name' => Setting::get('head_name'),
                'head_rank' => Setting::get('head_rank'),
                'head_nip' => Setting::get('head_nip'),
            ],
            'letterNumber' => $report->letterNumber(),
            'generatedAt' => now(),
            'followUpSummary' => $report->follow_up ?: 'Belum ada tindak lanjut yang dicatat pada sistem.',
            'recommendation' => $report->recommendation ?: 'Belum ada saran tindak yang dicatat pada sistem.',
        ];
    }
}
