<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'avatar_path', 'village_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function submittedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'submitted_by_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function changedReportStatuses(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class, 'changed_by_id');
    }

    public function temporaryUploads(): HasMany
    {
        return $this->hasMany(TemporaryUpload::class);
    }

    /**
     * Resolve the public URL of the user's avatar, if any.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path !== null
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
