<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\District;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Support\ReportBadges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\WriterInterface;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    /**
     * Show the export form with its filters.
     */
    public function create(): View
    {
        return view('reports.export', [
            'districts' => District::query()->orderBy('name')->get(),
            'categories' => ReportCategory::query()->orderBy('name')->get(),
            'incidentTypes' => IncidentType::query()->with('reportCategory')->orderBy('name')->get(),
            'statuses' => ReportStatus::cases(),
        ]);
    }

    /**
     * Stream the filtered reports as an Excel or CSV download.
     */
    public function download(Request $request): BinaryFileResponse
    {
        $data = $request->validate([
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'report_category_id' => ['nullable', 'integer', 'exists:report_categories,id'],
            'incident_type_id' => ['nullable', 'integer', 'exists:incident_types,id'],
            'status' => ['nullable', Rule::in(array_map(fn (ReportStatus $status) => $status->value, ReportStatus::cases()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'format' => ['required', Rule::in(['xlsx', 'csv'])],
        ]);

        $format = $data['format'];
        $path = tempnam(sys_get_temp_dir(), 'sideni-export-');

        if ($path === false) {
            abort(500, 'Berkas ekspor tidak dapat disiapkan.');
        }

        $writer = $this->writer($format);
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues($this->headings()));

        foreach ($this->query($data)->lazy(500) as $report) {
            $writer->addRow(Row::fromValues($this->row($report)));
        }

        $writer->close();

        $filename = 'laporan-sideni-'.now()->format('Ymd-His').'.'.$format;
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()
            ->download($path, $filename, ['Content-Type' => $contentType])
            ->deleteFileAfterSend();
    }

    private function writer(string $format): WriterInterface
    {
        return $format === 'csv'
            ? new CsvWriter(new CsvOptions)
            : new XlsxWriter(new XlsxOptions);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Builder<Report>
     */
    private function query(array $data): Builder
    {
        return Report::query()
            ->visibleTo(auth()->user())
            ->with(['village.district', 'reportCategory', 'incidentType', 'submittedBy'])
            ->orderBy('created_at')
            ->when($data['district_id'] ?? null, fn (Builder $query, $district) => $query->whereHas('village', fn (Builder $village) => $village->where('district_id', $district)))
            ->when($data['report_category_id'] ?? null, fn (Builder $query, $category) => $query->where('report_category_id', $category))
            ->when($data['incident_type_id'] ?? null, fn (Builder $query, $type) => $query->where('incident_type_id', $type))
            ->when($data['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($data['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($data['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
    }

    /**
     * @return array<int, string>
     */
    private function headings(): array
    {
        return [
            'Nomor Registrasi',
            'Tanggal Laporan',
            'Kecamatan',
            'Kampung',
            'Kategori Kerawanan',
            'Jenis Kejadian',
            'Tingkat Risiko',
            'Status',
            'Waktu Dilaporkan',
            'Waktu Kejadian',
            'Lokasi Kejadian',
            'Latitude',
            'Longitude',
            'Nama Pelapor',
            'No. Telepon Pelapor',
            'Uraian Kejadian',
            'Tindak Lanjut',
            'Saran Tindakan',
            'Dilaporkan Oleh',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function row(Report $report): array
    {
        $canViewIdentity = $report->identityVisibleTo(auth()->user());

        return [
            $report->registration_number,
            $report->created_at?->format('d/m/Y H:i') ?? '-',
            $report->village?->district?->name ?? '-',
            $report->village?->name ?? '-',
            $report->reportCategory?->name ?? '-',
            $report->incidentType?->name ?? '-',
            ReportBadges::riskLabel($report),
            ReportBadges::statusLabel($report),
            $report->reported_at?->format('d/m/Y H:i') ?? '-',
            $report->occurred_at?->format('d/m/Y H:i') ?? '-',
            $report->location_name,
            (string) $report->latitude,
            (string) $report->longitude,
            $canViewIdentity ? $report->reporter_name : $report->maskedReporterName(),
            $canViewIdentity ? ($report->reporter_phone ?? '-') : ($report->maskedReporterPhone() ?? '-'),
            $report->description,
            $report->follow_up ?? '-',
            $report->recommendation ?? '-',
            $report->submittedBy?->name ?? '-',
        ];
    }
}
