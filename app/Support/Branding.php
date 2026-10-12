<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class Branding
{
    /**
     * Default application logo shipped in the public directory.
     */
    public const DEFAULT_LOGO = 'Lambang_Kabupaten_Way_Kanan.png';

    /**
     * Public URL of the active logo (uploaded institution logo, else the default emblem).
     */
    public static function logoUrl(): string
    {
        $logo = Setting::get('institution_logo');

        if ($logo !== null && Storage::disk('public')->exists($logo)) {
            return Storage::disk('public')->url($logo);
        }

        return asset(self::DEFAULT_LOGO);
    }

    /**
     * Local filesystem path of the active logo (for PDF rendering), or null when unavailable.
     */
    public static function logoPath(): ?string
    {
        $logo = Setting::get('institution_logo');

        if ($logo !== null && Storage::disk('public')->exists($logo)) {
            return Storage::disk('public')->path($logo);
        }

        $default = public_path(self::DEFAULT_LOGO);

        return file_exists($default) ? $default : null;
    }
}
