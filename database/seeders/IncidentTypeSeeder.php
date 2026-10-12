<?php

namespace Database\Seeders;

use App\Models\IncidentType;
use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

class IncidentTypeSeeder extends Seeder
{
    /**
     * Seed incident types grouped under each report category.
     *
     * @var array<string, array<int, string>>
     */
    private array $types = [
        'TANAH' => [
            'Sengketa Batas Tanah',
            'Sengketa Kepemilikan Lahan',
            'Penggusuran / Pembebasan Lahan',
            'Sengketa Lahan Adat',
            'Konflik Lahan Perkebunan',
            'Sengketa Tanah Ulayat',
            'Tumpang Tindih Sertifikat',
            'Pendudukan Lahan Ilegal',
        ],
        'TIBUM' => [
            'Tawuran Antar Warga',
            'Perselisihan Warga',
            'Pesta Miras',
            'Balap Liar',
            'Pelanggaran Ketertiban Umum',
            'Kebisingan / Gangguan Lingkungan',
            'Parkir Liar / Kemacetan',
            'Perkelahian Remaja',
        ],
        'SDA' => [
            'Pencemaran Air / Sungai',
            'Penebangan Liar',
            'Konflik Air Irigasi',
            'Perambahan Hutan',
            'Pertambangan Ilegal',
            'Pencemaran Udara',
            'Penangkapan Ikan Ilegal',
            'Konflik Lahan Konsesi',
        ],
        'EKON' => [
            'Perselisihan Hubungan Kerja',
            'Upah Tidak Dibayar',
            'Persaingan Usaha Tidak Sehat',
            'PHK Massal',
            'Aksi Demo Buruh',
            'Monopoli / Kartel',
            'Perselisihan Bagi Hasil',
            'Kenaikan Harga Sembako',
        ],
        'POL' => [
            'Konflik Pilkada',
            'Perselisihan Antar Pendukung',
            'Sengketa Batas Wilayah Kampung',
            'Polemik Kebijakan Publik',
            'Konflik Kepengurusan Organisasi',
            'Sengketa Hasil Pemilihan',
            'Provokasi Politik',
        ],
        'SOSBUD' => [
            'Konflik Antar Etnis',
            'Perselisihan Adat',
            'Konflik Antar Kelompok',
            'Perselisihan Remaja',
            'Konflik Antar Organisasi Masyarakat',
            'Perselisihan Rumah Tangga',
            'Konflik di Media Sosial',
        ],
        'KAMTIB' => [
            'Pencurian',
            'Perjudian',
            'Penyalahgunaan Narkoba',
            'Premanisme',
            'Kekerasan',
            'Pungutan Liar',
            'Senjata Tajam / Senjata Api',
            'Perdagangan Orang',
        ],
        'AGAMA' => [
            'Pendirian Rumah Ibadah',
            'Penyebaran Aliran Kepercayaan',
            'Konflik Antar Umat Beragama',
            'Penistaan Agama',
            'Konflik Peribadatan',
            'Aliran Sesat',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->types as $categoryCode => $names) {
            $category = ReportCategory::query()->where('code', $categoryCode)->first();

            if ($category === null) {
                continue;
            }

            foreach ($names as $index => $name) {
                IncidentType::query()->updateOrCreate(
                    ['code' => $categoryCode.'-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                    [
                        'report_category_id' => $category->id,
                        'name' => $name,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}
