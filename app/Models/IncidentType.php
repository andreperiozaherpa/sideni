<?php

namespace App\Models;

use Database\Factories\IncidentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['report_category_id', 'code', 'name', 'is_active', 'sort_order'])]
class IncidentType extends Model
{
    /** @use HasFactory<IncidentTypeFactory> */
    use HasFactory;

    public function reportCategory(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
