<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Database\Factories\ReportStatusHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'changed_by_id', 'from_status', 'to_status', 'note'])]
class ReportStatusHistory extends Model
{
    /** @use HasFactory<ReportStatusHistoryFactory> */
    use HasFactory;

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

    protected function casts(): array
    {
        return [
            'from_status' => ReportStatus::class,
            'to_status' => ReportStatus::class,
        ];
    }
}
