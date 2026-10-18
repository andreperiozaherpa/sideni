<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\District;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use App\Support\ReportBadges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     * Show the export form with its filters, scoped to the user's region.
     */
    public function create(): View
    {
        $user = auth()->user();
        $scope = $this->scope($user);

        return view('reports.export', [
            'scope' => $scope,
            'districts' => $this->districtsFor($user, $scope),
            'villages' => $this->villagesFor($user, $scope),
            'categories' => ReportCategory::query()->orderBy('name')->get(),
            'incidentTypes' => IncidentType::query()->with('reportCategory')->orderBy('name')->get(),
            'statuses' => ReportStatus::cases(),
        ]);
    }

    /**
     * Stream the filtered reports as an Excel or CSV download.
     */
    public function download(Request $request): BinaryFileResponse|RedirectResponse
    {
        $user = auth()->user();

        if ($this->scope($user) === 'none') {
            return redirect()->route('reports.export')->withErrors([
                'region' => 'Akun Anda belum ditetapkan wilayah (kecamatan/kampung). Hubungi administrator sebelum mengekspor laporan.',
            ]);
        }

        $data = $request->validate([
            'village_id' => ['nullable', 'integer', 'exists:villages,id'],
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

        foreach ($this->query($data, $user)->lazy(500) as $report) {
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

    /**
     * How far the user's export reaches: all regions, one village, one district, or nothing.
     */
    private function scope(User $user): string
    {
        if ($user->hasFullReportAccess()) {
            return 'all';
        }

        if ($user->village_id !== null) {
            return 'village';
        }

        if ($user->district_id !== null) {
            return 'district';
        }

        return 'none';
    }

    /**
     * @return Collection<int, District>
     */
    private function districtsFor(User $user, string $scope): Collection
    {
        return match ($scope) {
            'all' => District::query()->orderBy('name')->get(),
            'village' => District::query()->whereKey($user->village?->district_id)->get(),
            'district' => District::query()->whereKey($user->district_id)->get(),
            default => new Collection,
        };
    }

    /**
     * @return Collection<int, Village>
     */
    private function villagesFor(User $user, string $scope): Collection
    {
        return match ($scope) {
            'all' => Village::query()->where('is_active', true)->orderBy('name')->get(),
            'village' => Village::query()->whereKey($user->village_id)->get(),
            'district' => Village::query()->where('district_id', $user->district_id)->where('is_active', true)->orderBy('name')->get(),
            default => new Collection,
        };
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
    private function query(array $data, User $user): Builder
    {
        $query = Report::query()
            ->with(['village.district', 'reportCategory', 'incidentType', 'submittedBy'])
            ->orderBy('created_at');

        $this->applyRegionScope($query, $user);

        return $query
            ->when($data['district_id'] ?? null, fn (Builder $query, $district) => $query->whereHas('village', fn (Builder $village) => $village->where('district_id', $district)))
            ->when($data['village_id'] ?? null, fn (Builder $query, $village) => $query->where('village_id', $village))
            ->when($data['report_category_id'] ?? null, fn (Builder $query, $category) => $query->where('report_category_id', $category))
            ->when($data['incident_type_id'] ?? null, fn (Builder $query, $type) => $query->where('incident_type_id', $type))
            ->when($data['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($data['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($data['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
    }

    /**
     * Limit the export to the user's own region unless they may see every region.
     *
     * @param  Builder<Report>  $query
     */
    private function applyRegionScope(Builder $query, User $user): void
    {
        if ($user->hasFullReportAccess()) {
            return;
        }

        if ($user->village_id !== null) {
            $query->where('village_id', $user->village_id);

            return;
        }

        if ($user->district_id !== null) {
            $query->whereHas('village', fn (Builder $village) => $village->where('district_id', $user->district_id));

            return;
        }

        $query->whereRaw('1 = 0');
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
