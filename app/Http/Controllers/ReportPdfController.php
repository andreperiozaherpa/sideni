<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Support\ReportPdfData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    /**
     * Final detection report for the Regent, signed by the head of the agency.
     */
    public function bupati(Report $report): Response
    {
        abort_unless(auth()->user()->can('export reports'), 403);
        $this->ensureVerified($report);

        return $this->render('pdf.bupati', $report, 'laporan-deteksi-dini-'.$report->registration_number.'.pdf');
    }

    /**
     * Acknowledgement and recommendation letter for the reporting village.
     */
    public function notice(Report $report): Response
    {
        abort_unless(
            $report->submitted_by_id === auth()->id()
            || auth()->user()->can('verify reports')
            || auth()->user()->can('view all reports'),
            403,
        );
        $this->ensureVerified($report);

        return $this->render('pdf.notice', $report, 'surat-pemberitahuan-'.$report->registration_number.'.pdf');
    }

    private function render(string $view, Report $report, string $filename): Response
    {
        return Pdf::loadView($view, ReportPdfData::make($report))->setPaper('a4')->download($filename);
    }

    /**
     * Documents are only available once the report has been verified.
     */
    private function ensureVerified(Report $report): void
    {
        abort_unless(
            in_array($report->status, [ReportStatus::Verified, ReportStatus::Completed], true),
            403,
            'Dokumen hanya tersedia setelah laporan diverifikasi.',
        );
    }
}
