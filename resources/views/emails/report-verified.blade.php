@component('mail::message')
# Laporan Diverifikasi

Halo {{ $report->submittedBy?->name ?? 'Sahabat' }},

Laporan Anda dengan nomor registrasi **{{ $report->registration_number }}** telah **diverifikasi** oleh Admin Kesbangpol.

- **Kategori:** {{ $report->reportCategory?->name ?? '-' }}
- **Jenis kejadian:** {{ $report->incidentType?->name ?? '-' }}
- **Kampung:** {{ $report->village?->name ?? '-' }}
- **Status:** {{ str($report->status->value)->replace('_', ' ')->title() }}

Surat Pemberitahuan dan Rekomendasi terlampir pada email ini.

Terima kasih,
{{ config('app.name') }}
@endcomponent
