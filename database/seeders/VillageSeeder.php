<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    /**
     * Seed the kampung and kelurahan of every kecamatan in Kabupaten Way Kanan.
     *
     * Source: Daftar kecamatan dan kelurahan di Kabupaten Way Kanan (BPS/Kemendagri).
     */
    public function run(): void
    {
        $districts = [
            '18.08.01' => [
                'kampung' => ['Bumi Baru', 'Gunung Sangkaran', 'Karang Umpu', 'Lembasung', 'Sangkaran Bhakti', 'Segara Mider', 'Sri Rejeki', 'Tanjung Raja Giham', 'Tanjung Raja Sakti', 'Tanjung Sari', 'Umpu Bhakti', 'Umpu Kencana'],
                'kelurahan' => ['Blambangan Umpu'],
            ],
            '18.08.02' => [
                'kampung' => ['Bukitbatu', 'Datar Bancong', 'Gelombang Panjang', 'Jaya Tinggi', 'Juku Kemuning', 'Kampung Baru', 'Karang Lantang', 'Kasui Lama', 'Kedaton', 'Kota Way', 'Sinar Gading', 'Sukajadi', 'Talang Mangga', 'Tangkas', 'Tanjung Bulan', 'Tanjung Harapan', 'Tanjung Kurung', 'Tanjungkurung Lama'],
                'kelurahan' => ['Kasui Pasar'],
            ],
            '18.08.03' => [
                'kampung' => ['Argomulyo', 'Bali Sadar Selatan', 'Bali Sadar Tengah', 'Bali Sadar Utara', 'Bandar Agung', 'Bonglai', 'Campang Delapan', 'Dono Mulyo', 'Juku Batu', 'Kemu', 'Menanga Jaya', 'Menanga Siamang', 'Neki', 'Rantau Jaya', 'Rantau Temiang', 'Rebang Tinggi', 'Simpang Asam', 'Sumber Baru', 'Sumber Sari'],
                'kelurahan' => ['Pasar Banjit'],
            ],
            '18.08.04' => [
                'kampung' => ['Bakti Negara', 'Banjar Agung', 'Banjar Baru', 'Banjar Masin', 'Banjar Mulya', 'Banjar Negara', 'Banjar Sari', 'Banjar Setia', 'Bumi Merapi', 'Bumi Rejo', 'Cugah', 'Gedung Pakuon', 'Gedung Rejo', 'Gunung Katun', 'Mekar Asri', 'Setia Negara', 'Sukosari', 'Tiuh Balak', 'Tiuh Balak Satu'],
                'kelurahan' => ['Campur Asri', 'Taman Asri', 'Tiuh Balak Pasar'],
            ],
            '18.08.05' => [
                'kampung' => ['Bumi Agung', 'Bumi Agung Wates', 'Dewa Agung', 'Giriharjo', 'Mekar Jaya', 'Mesir Ilir', 'Mesir Udik', 'Putra Dewa', 'Saptorenggo', 'Serdang Kuring', 'Tulang Bawang'],
            ],
            '18.08.06' => [
                'kampung' => ['Bakti Negara', 'Bumi Mulya', 'Gunung Cahya', 'Gunung Waras', 'Karang Agung', 'Negara Harja', 'Negara Ratu', 'Negara Sakti', 'Negara Tama', 'Pakuan Baru', 'Pakuan Ratu', 'Pakuan Sakti', 'Rumbih', 'Serupa Indah', 'Suka Bumi', 'Tanjung Agung', 'Tanjung Ratu', 'Tanjung Serupa', 'Way Tawar'],
            ],
            '18.08.07' => [
                'kampung' => ['Bandar Dalam', 'Bandar Kasih', 'Gedung Harapan', 'Gedung Jaya', 'Gedung Menong', 'Kali Papan', 'Karya Agung', 'Kota Baru', 'Kotabumi Way Kanan', 'Mulya Agung', 'Mulya Sari', 'Negeri Agung', 'Penengahan', 'Pulau Batu', 'Rejosari', 'Sumber Rejeki', 'Sungsang', 'Tanjung Rejo', 'Way Limau'],
            ],
            '18.08.08' => [
                'kampung' => ['Bandar Sari', 'Beringin Jaya', 'Bukit Gemuruh', 'Bukit Harapan', 'Bumi Dana', 'Karya Jaya', 'Ramsai', 'Say Umpu', 'Suma Mukti', 'Way Mencar', 'Way Pisang', 'Way Tuba', 'Way Tuba Asri'],
            ],
            '18.08.09' => [
                'kampung' => ['Air Ringkih', 'Beringin Jaya', 'Gunung Sari', 'Karya Maju', 'Lebak Peniangan', 'Madang Jaya', 'Mulya Jaya', 'Simpang Tiga', 'Tanjung Raya', 'Tanjung Tiga'],
            ],
            '18.08.10' => [
                'kampung' => ['Banjar Ratu', 'Banjar Sakti', 'Bengkulu', 'Bengkulu Jaya', 'Bengkulu Raman', 'Bengkulu Rejo', 'Bengkulu Tengah', 'Curup Patah', 'Gunung Baru', 'Gunung Labuhan', 'Gunung Sari', 'Gunungpekuwon', 'Kayu Batu', 'Labuhan Jaya', 'Negeri Mulyo', 'Negeri Sungkai', 'Negeri Ujan Mas', 'Suka Negeri', 'Sukarame', 'Tiuh Balak II', 'Way Tuba'],
            ],
            '18.08.11' => [
                'kampung' => ['Adi Jaya', 'Bumi Jaya', 'Gedung Jaya', 'Gisting Jaya', 'Karta Jaya', 'Kota Jawa', 'Marga Jaya', 'Negara Batin', 'Negara Mulya', 'Purwa Agung', 'Purwa Negara', 'Sari Jaya', 'Setia Negara', 'Sri Menanti', 'Sri Mulyo'],
            ],
            '18.08.12' => [
                'kampung' => ['Bima Sakti', 'Kaliawi', 'Kaliawi Indah', 'Kiling-kiling', 'Negara Jaya', 'Negeri Besar', 'Negeri Jaya', 'Negeri Kasih', 'Pagar Iman', 'Sri Basuki', 'Tanjungmas', 'Tegal Mukti', 'Tiuh Baru'],
            ],
            '18.08.13' => [
                'kampung' => ['Bumiharjo', 'Lebung Lawe', 'Nuar Maju', 'Punjul Agung', 'Sri Tunggal', 'Suka Agung', 'Sukabumi', 'Sukadana', 'Way Agung'],
            ],
            '18.08.14' => [
                'kampung' => ['Bumi Agung', 'Bumi Say Agung', 'Karangan', 'Mulyo Harjo', 'Pisang Baru', 'Pisang Indah', 'Sri Numpi', 'Suka Maju', 'Tanjung Dalam', 'Wonoharjo'],
            ],
            '18.08.15' => [
                'kampung' => ['Bratayudha', 'Bumi Ratu', 'Gedung Batin', 'Gedung Riang', 'Gistang', 'Negeri Baru', 'Negeri Batin', 'Negeri Bumi Putra', 'Ojo Lali', 'Panca Negeri', 'Rambang Jaya', 'Sido Arjo', 'Sriwijaya'],
            ],
        ];

        foreach ($districts as $districtCode => $groups) {
            $district = District::query()->where('code', $districtCode)->firstOrFail();
            $sortOrder = 0;

            foreach ($groups['kelurahan'] ?? [] as $index => $name) {
                Village::query()->updateOrCreate(
                    ['code' => $districtCode.'.'.(1001 + $index)],
                    ['district_id' => $district->id, 'name' => $name, 'is_active' => true, 'sort_order' => ++$sortOrder],
                );
            }

            foreach ($groups['kampung'] ?? [] as $index => $name) {
                Village::query()->updateOrCreate(
                    ['code' => $districtCode.'.'.(2001 + $index)],
                    ['district_id' => $district->id, 'name' => $name, 'is_active' => true, 'sort_order' => ++$sortOrder],
                );
            }
        }
    }
}
