<?php

namespace App\Models;

use Database\Factories\ReportAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['disk', 'path', 'original_name', 'mime_type', 'size'])]
class ReportAttachment extends Model
{
    /** @use HasFactory<ReportAttachmentFactory> */
    use HasFactory;

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }
}
