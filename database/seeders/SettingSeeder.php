<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the default operational parameters without overwriting existing values.
     */
    public function run(): void
    {
        $defaults = [
            'institution_name' => 'Badan Kesatuan Bangsa dan Politik',
            'institution_address' => 'Jl. Widana Abu Bakar Nomor 19 Komplek Perkantoran Pemda KM.2 Blambangan Umpu – 34564',
            'institution_phone' => null,
            'institution_email' => null,
            'head_name' => 'ARIFIN, S.Sos., M.M.',
            'head_rank' => 'Pembina Utama Muda',
            'head_nip' => '19701210 199101 1 001',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
