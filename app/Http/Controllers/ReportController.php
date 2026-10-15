<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\StoreReportRequest;
use App\Mail\ReportVerifiedMail;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\TemporaryUpload;
use App\Models\Village;
use App\Support\ReportBadges;
use App\Support\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function __construct(private readonly UploadService $uploads) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Report::query()->visibleTo(auth()->user())->with('village', 'reportCategory');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($request->ajax()) {
            return DataTables::eloquent($query->orderByDesc('created_at')->orderByDesc('id'))
                ->filterColumn('village_name', fn ($query, $keyword) => $query->whereHas('village', fn ($query) => $query->where('name', 'like', "%{$keyword}%")))
                ->filterColumn('category_name', fn ($query, $keyword) => $query->whereHas('reportCategory', fn ($query) => $query->where('name', 'like', "%{$keyword}%")))
                ->filterColumn('incident_name', fn ($query, $keyword) => $query->whereHas('incidentType', fn ($query) => $query->where('name', 'like', "%{$keyword}%")))
                ->filterColumn('risk_label', fn ($query, $keyword) => $query->where('risk_level', 'like', '%'.(['rendah' => 'low', 'sedang' => 'medium', 'tinggi' => 'high'][strtolower($keyword)] ?? strtolower($keyword)).'%'))
                ->filterColumn('status_label', fn ($query, $keyword) => $query->where('status', 'like', '%'.strtolower($keyword).'%'))
                ->addColumn('village_name', fn (Report $report) => $report->village?->name ?? '-')
                ->addColumn('category_name', fn (Report $report) => $report->reportCategory?->name ?? '-')
                ->addColumn('risk_label', fn (Report $report) => str($report->risk_level->value)->title())
                ->addColumn('status_label', fn (Report $report) => str($report->status->value)->replace('_', ' ')->title())
                ->addColumn('created_label', fn (Report $report) => $report->created_at?->format('d/m/Y H:i'))
                ->addColumn('sla', fn (Report $report) => ReportBadges::sla($report))
                ->addColumn('action', fn (Report $report) => $this->reportActions($report))
                ->rawColumns(['sla', 'action'])
                ->toJson();
        }

        return view('reports.index', [
            'statusCounts' => $this->statusCounts(),
            'currentStatus' => $request->string('status')->toString(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        return Report::query()->visibleTo(auth()->user())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('reports.form', [
            'report' => new Report,
            'villages' => Village::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => ReportCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportRequest $request): RedirectResponse
    {
        $report = DB::transaction(function () use ($request): Report {
            $year = now()->year;
            $sequence = Report::query()
                ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])
                ->lockForUpdate()
                ->count() + 1;

            $report = Report::create([
                ...$request->safe()->except('attachments'),
                'submitted_by_id' => auth()->id(),
                'registration_number' => sprintf('SD-%d-%04d', $year, $sequence),
                'status' => ReportStatus::Pending,
            ]);
            $report->statusHistories()->create(['to_status' => ReportStatus::Pending->value, 'changed_by_id' => auth()->id(), 'note' => 'Laporan dibuat.']);
            $this->storeAttachments($report, $request->file('attachments', []), $request->input('uploaded_files', []));

            return $report;
        });

        return redirect()->route('reports.show', $report)->with('status', 'Laporan berhasil dikirim.');
    }

    /**
     * Show the form for editing a report that needs clarification.
     */
    public function edit(Report $report): View
    {
        $this->authorizeClarification($report);

        return view('reports.form', [
            'report' => $report->load('attachments'),
            'villages' => Village::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => ReportCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update a report that needs clarification and return it to the verification queue.
     */
    public function update(StoreReportRequest $request, Report $report): RedirectResponse
    {
        $this->authorizeClarification($report);
        $fromStatus = $report->status->value;

        $report->update($request->safe()->except('attachments'));
        $this->storeAttachments($report, $request->file('attachments', []), $request->input('uploaded_files', []));

        $report->status = ReportStatus::Pending;
        $report->save();
        $report->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => ReportStatus::Pending->value,
            'changed_by_id' => auth()->id(),
            'note' => 'Perbaikan data dikirim ulang.',
        ]);

        return redirect()->route('reports.show', $report)->with('status', 'Perbaikan laporan berhasil dikirim ulang.');
    }

    public function show(Report $report): View
    {
        abort_unless(
            $report->submitted_by_id === auth()->id()
            || auth()->user()->can('verify reports')
            || auth()->user()->can('view all reports'),
            404,
        );

        return view('reports.show', [
            'report' => $report->load('village.district', 'reportCategory', 'incidentType', 'submittedBy', 'attachments', 'statusHistories.changedBy'),
        ]);
    }

    public function verify(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'follow_up' => ['nullable', 'string', 'max:5000'],
            'recommendation' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $report->update([
            'follow_up' => $data['follow_up'] ?? null,
            'recommendation' => $data['recommendation'] ?? null,
        ]);

        $response = $this->changeStatus($report, ReportStatus::Verified, $data['note'] ?? 'Laporan telah diverifikasi.');

        $this->sendVerifiedNotice($report);

        return $response;
    }

    private function sendVerifiedNotice(Report $report): void
    {
        $submitter = $report->submittedBy;

        if ($submitter === null || $submitter->email === null) {
            return;
        }

        try {
            Mail::to($submitter->email)->queue(new ReportVerifiedMail($report));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function clarify(Request $request, Report $report): RedirectResponse
    {
        return $this->changeStatus($report, ReportStatus::NeedsClarification, $request->string('note')->toString() ?: 'Data atau bukti perlu dilengkapi.');
    }

    public function reject(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:5000']]);

        return $this->changeStatus($report, ReportStatus::Rejected, $data['note']);
    }

    public function complete(Request $request, Report $report): RedirectResponse
    {
        return $this->changeStatus($report, ReportStatus::Completed, $request->string('note')->toString() ?: 'Penanganan laporan dinyatakan selesai.');
    }

    private function changeStatus(Report $report, ReportStatus $status, string $note): RedirectResponse
    {
        $fromStatus = $report->status->value;
        abort_unless($report->status->canTransitionTo($status), 422, 'Transisi status tidak valid.');

        $report->status = $status;
        $report->save();
        $report->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $status->value,
            'changed_by_id' => auth()->id(),
            'note' => $note,
        ]);

        return redirect()->route('reports.show', $report)->with('status', 'Status laporan berhasil diperbarui.');
    }

    private function authorizeClarification(Report $report): void
    {
        abort_unless(
            $report->submitted_by_id === auth()->id() && $report->status === ReportStatus::NeedsClarification,
            403,
            'Hanya pelapor dapat melengkapi laporan yang berstatus perlu klarifikasi.',
        );
    }

    private function reportActions(Report $report): string
    {
        $html = '<div class="flex flex-wrap gap-3"><a class="link" href="'.route('reports.show', $report).'">Lihat</a>';

        if ($report->submitted_by_id === auth()->id() && $report->status === ReportStatus::NeedsClarification) {
            $html .= '<a class="font-semibold text-amber-700" href="'.route('reports.edit', $report).'">Lengkapi</a>';
        }

        if (auth()->user()->can('verify reports') && $report->status === ReportStatus::Pending) {
            $html .= '<a class="font-semibold text-sky-700" href="'.route('reports.verify.show', $report).'">Verifikasi</a>';
        }

        return $html.'</div>';
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>  $tokens
     */
    private function storeAttachments(Report $report, array $files, array $tokens = []): void
    {
        foreach ($files as $attachment) {
            $report->attachments()->create($this->uploads->store($attachment, 'reports/'.$report->id, 'local'));
        }

        if ($tokens === []) {
            return;
        }

        $temporaryUploads = TemporaryUpload::query()
            ->where('user_id', auth()->id())
            ->whereIn('token', $tokens)
            ->get();

        foreach ($temporaryUploads as $temporaryUpload) {
            $report->attachments()->create($this->uploads->move(
                $temporaryUpload->disk,
                $temporaryUpload->path,
                'reports/'.$report->id,
                $temporaryUpload->original_name,
                $temporaryUpload->mime_type,
                $temporaryUpload->size,
            ));

            $temporaryUpload->delete();
        }
    }
}
