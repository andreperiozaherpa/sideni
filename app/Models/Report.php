<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\RiskLevel;
use App\Notifications\ReportStatusChanged;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable([
    'submitted_by_id',
    'registration_number',
    'village_id',
    'report_category_id',
    'incident_type_id',
    'reporter_name',
    'reporter_phone',
    'reported_at',
    'occurred_at',
    'location_name',
    'latitude',
    'longitude',
    'description',
    'risk_level',
    'status',
    'follow_up',
    'recommendation',
])]
#[Hidden(['reporter_phone'])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function reportCategory(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class);
    }

    public function incidentType(): BelongsTo
    {
        return $this->belongsTo(IncidentType::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }

    /**
     * Limit the query to the reports the given user is allowed to see.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->can('verify reports') || $user->can('view all reports')) {
            return;
        }

        $query->where('submitted_by_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'reporter_phone' => 'encrypted',
            'reported_at' => 'datetime',
            'occurred_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'risk_level' => RiskLevel::class,
            'status' => ReportStatus::class,
        ];
    }

    /**
     * Keep the stage timestamp in sync whenever the report status changes.
     */
    protected static function booted(): void
    {
        static::saving(function (Report $report): void {
            if ($report->isDirty('status')) {
                $report->status_changed_at = now();
            }
        });

        static::saved(function (Report $report): void {
            if (! $report->wasChanged('status') || $report->getOriginal('status') === null) {
                return;
            }

            $submitter = $report->submittedBy;

            if ($submitter === null) {
                return;
            }

            $actor = auth()->user();

            if ($actor !== null && $actor->is($submitter)) {
                return;
            }

            try {
                $submitter->notify(new ReportStatusChanged($report));
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }

    /**
     * Deadline for the report's current stage, based on the configured SLA.
     */
    public function slaDeadline(): ?Carbon
    {
        $config = config('sideni.sla.'.$this->status->value);

        if ($config === null || $this->status_changed_at === null) {
            return null;
        }

        return $this->status_changed_at->copy()->addMinutes($config['minutes']);
    }

    /**
     * SLA state for the current stage: ok, warning, overdue, or null when terminal.
     */
    public function slaState(): ?string
    {
        $config = config('sideni.sla.'.$this->status->value);
        $deadline = $this->slaDeadline();

        if ($config === null || $deadline === null) {
            return null;
        }

        if (now()->greaterThan($deadline)) {
            return 'overdue';
        }

        return now()->diffInMinutes($deadline, false) <= max(1, $config['minutes']) * 0.25 ? 'warning' : 'ok';
    }

    /**
     * Determine whether the given user may read the reporter's full identity.
     */
    public function identityVisibleTo(?User $user = null): bool
    {
        return (bool) $user?->can('view reporter identity');
    }

    /**
     * Get the reporter's name with its leading characters masked for non-privileged viewers.
     */
    public function maskedReporterName(): string
    {
        return Str::mask($this->reporter_name, '*', 2);
    }

    /**
     * Get the reporter's phone number with its trailing characters masked for non-privileged viewers.
     */
    public function maskedReporterPhone(): ?string
    {
        return $this->reporter_phone === null ? null : Str::mask($this->reporter_phone, '*', 4);
    }

    /**
     * Build the automatic outgoing letter number: 300/{sequence}/V.06-WK/{year}.
     */
    public function letterNumber(): string
    {
        $year = $this->created_at?->year ?? now()->year;
        $sequence = Str::afterLast($this->registration_number, '-');

        return sprintf('300/%s/V.06-WK/%d', $sequence, $year);
    }
}
