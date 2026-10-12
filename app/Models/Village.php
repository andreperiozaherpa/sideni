<?php

namespace App\Models;

use Database\Factories\VillageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['district_id', 'code', 'name', 'is_active', 'sort_order'])]
class Village extends Model
{
    /** @use HasFactory<VillageFactory> */
    use HasFactory;

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
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
