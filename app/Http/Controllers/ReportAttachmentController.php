<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportAttachmentController extends Controller
{
    /**
     * Download a report attachment for a viewer allowed to see the report.
     */
    public function show(Report $report, ReportAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->report_id === $report->id, 404);
        abort_unless(
            $report->submitted_by_id === auth()->id()
            || auth()->user()->can('verify reports')
            || auth()->user()->can('view all reports'),
            404,
        );

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
