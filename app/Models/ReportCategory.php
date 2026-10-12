<?php

namespace App\Models;

use Database\Factories\ReportCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'is_active', 'sort_order'])]
class ReportCategory extends Model
{
    /** @use HasFactory<ReportCategoryFactory> */
    use HasFactory;

    public function incidentTypes(): HasMany
    {
        return $this->hasMany(IncidentType::class);
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
