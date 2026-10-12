<?php

namespace App\Mail;

use App\Models\Report;
use App\Support\ReportPdfData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReportVerifiedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan '.$this->report->registration_number.' telah diverifikasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.report-verified',
            with: ['report' => $this->report],
        );
    }

    /**
     * Attach the Surat Pemberitahuan & Rekomendasi PDF.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.notice', ReportPdfData::make($this->report))
            ->setPaper('a4')
            ->output();

        return [
            Attachment::fromData(fn () => $pdf, 'surat-pemberitahuan-'.$this->report->registration_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
