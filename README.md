# SI-DENI WAY KANAN (Sistem Deteksi Dini Konflik Sosial Terintegrasi)

**SI-DENI** adalah aplikasi pelaporan dan manajemen deteksi dini potensi konflik sosial berbasis web. Aplikasi ini dirancang khusus untuk Badan Kesatuan Bangsa dan Politik (Bakesbangpol) Kabupaten Way Kanan guna mempercepat proses penghimpunan, verifikasi, analisis, dan tindak lanjut laporan dari tingkat kampung hingga pimpinan daerah.

---

## 🚀 Fitur Utama Aplikasi

Berdasarkan SOP Penanganan Konflik Sosial, aplikasi ini harus memiliki modul dan fitur berikut:

### 1. Modul Autentikasi, Manajemen Pengguna & Profil (RBAC)

- **Multi-Role Login**: Akses yang dibedakan berdasarkan peran pengguna (Aparatur Kampung, Admin Kesbangpol, Kepala Badan, Camat/Forkopimcam).
- **Manajemen Pengguna & Role**: Antarmuka khusus (CRUD) untuk mengelola data _user_, penugasan _role_ (hak akses), dan pengaturan _permission_ dinamis (menggunakan `spatie/laravel-permission`).
- **Halaman Profil Pengguna**: Halaman _User Profile_ yang memungkinkan pengguna memperbarui informasi akun, foto profil, dan mengganti _password_ secara mandiri.

### 2. Modul Pelaporan (Entry Laporan)

- **Form Pelaporan Geotagging**: Input laporan wajib menyertakan titik koordinat (Latitude/Longitude) otomatis atau manual via integrasi peta (Leaflet.js/OpenStreetMap).
- **Kategorisasi Isu**: Dropdown klasifikasi kerawanan (Pertanahan, Ketertiban Umum, SDA, dll) dan tingkat risiko (Rendah, Sedang, Tinggi).
- **Identitas Tersembunyi**: Input NIK dan nama pelapor (masyarakat) yang aman dan hanya dapat dilihat oleh level otoritas tertentu.

### 3. Standar UI/UX, Tabel & Interaksi Form

- **Manajemen Tabel (DataTables Server-Side)**: Seluruh penyajian data (seperti daftar laporan, manajemen pengguna) menggunakan **DataTables** dengan pemrosesan _server-side_ (`yajra/laravel-datatables`). Hal ini memastikan performa yang cepat dan ringan meskipun memuat ribuan data, lengkap dengan kemampuan _pagination_ otomatis, _search_ (pencarian global maupun per kolom), dan _sorting_ data.
- **Dropdown Interaktif**: Seluruh form input dengan tipe `select` (seperti pemilihan kategori, nama kampung, dll) menggunakan **Select2** yang dilengkapi fitur pencarian (_search_) untuk mempermudah pemilihan data.
- **Notifikasi Sistem (Alert)**: Menggunakan **SweetAlert2** untuk menampilkan respons interaktif pada setiap aksi pengguna (contoh: notifikasi sukses menyimpan data, konfirmasi sebelum menghapus data, dan peringatan _error_ sistem).
- **Upload Eviden Real-time (Dropzone.js)**: Mendukung unggahan foto bukti kejadian dan dokumen menggunakan **Dropzone.js**. Proses _upload_ berjalan secara asinkronus (_real-time_) ke penyimpanan sementara tanpa harus menunggu pengguna menekan tombol _submit_ form.

### 4. Modul Manajemen Alur Kerja (_State Machine_)

- Laporan memiliki alur status yang kaku (tidak bisa dilewati secara acak):
    1. `Pending`: Laporan baru masuk dari Aparatur Kampung.
    2. `Perlu Klarifikasi`: Dikembalikan ke Kampung untuk kelengkapan data.
    3. `Verified`: Disetujui Admin setelah verifikasi awal.
    4. `In Progress`: Tahap koordinasi/peninjauan lapangan oleh Forkopimcam.
    5. `Completed`: Masalah selesai / tertangani.
    6. `Rejected`: Laporan spam/tidak valid.

### 5. Arsitektur Unggahan Global (Global Upload Helper)

- Semua proses _upload_ file (baik untuk modul laporan, profil, maupun dokumen lainnya) ditangani terpusat oleh **Global Helper** atau **Global Controller**. Sistem ini akan secara otomatis memisahkan penyimpanan berdasarkan folder per modul dan melakukan standardisasi penamaan file, sehingga kode menjadi lebih rapi dan menghindari duplikasi fungsi _upload_ di berbagai _controller_.

### 6. Modul Cetak Dokumen Otomatis (PDF Export)

Aplikasi harus mampu meng-generate dokumen PDF secara dinamis dengan nomor surat otomatis (opsional: dilengkapi QR Code/Tanda Tangan Elektronik):

- **Laporan Hasil Deteksi Dini (Versi Bupati)**: Dokumen telaahan akhir yang ditandatangani Kepala Badan untuk dilaporkan ke Bupati.
- **Surat Pemberitahuan & Rekomendasi (Versi User)**: Bukti laporan diterima beserta rekomendasi tindakan pencegahan awal untuk Aparatur Kampung.
- **Lembar Disposisi Digital**: Untuk keperluan instruksi penanganan dari Kepala Badan ke Camat/Unsur terkait.

### 7. Modul Dashboard & Analitik

- **Visualisasi Grafik (Chart.js)**: Menampilkan statistik jumlah laporan masuk, laporan diproses, dan laporan selesai dalam bentuk grafik interaktif yang mudah dianalisis oleh Admin dan Kepala Badan.
- **Pemetaan Interaktif (Leaflet.js)**: Menyajikan peta sebaran potensi konflik yang menampilkan titik lokasi kejadian dan indikator kepadatan/intensitas banyaknya laporan di berbagai wilayah.
- **Dashboard Aparatur Kampung**: Menampilkan riwayat laporan yang pernah dikirim beserta status terkini.

### 8. Modul Data Master

- Menampung antarmuka pengelolaan data referensi yang akan digunakan sebagai acuan _dropdown_ pada form input laporan maupun pengaturan sistem. Data yang dikelola meliputi: Kategori Kerawanan, Jenis Kejadian, Data Kecamatan, Data Kampung, dan parameter operasional lainnya agar aplikasi bersifat dinamis.

### 9. Modul Notifikasi (Opsional namun Disarankan)

- Notifikasi sistem (In-App) atau integrasi WhatsApp Gateway/Email untuk memberitahu Aparatur Kampung ketika status laporannya berubah (misal: dari _Pending_ menjadi _Perlu Klarifikasi_).

### 10. Keamanan & Etika Data (Data Privacy)

Sesuai panduan penggunaan aplikasi, SI-DENI memegang prinsip **Cepat, Faktual, Terverifikasi, Berbasis Bukti, dan Tidak Menghakimi**[cite: 3]. Oleh karena itu, developer harus memperhatikan hal berikut:

- **Enkripsi/Masking NIK**: Data NIK dan identitas warga pelapor hanya digunakan untuk kebutuhan verifikasi awal dan bersifat sangat rahasia[cite: 10]. Data ini tidak boleh diekspos di sembarang _view_ atau disamarkan (_masked_) untuk _role_ di luar otoritas terkait.
- **Audit Trail/Logs**: Setiap perubahan status (dari verifikasi hingga tindak lanjut) harus terekam _timestamp_-nya dan siapa aktor yang mengubahnya (Log Riwayat Laporan)[cite: 4].
- **Format ID Registrasi Laporan**: Sistem harus men-generate kode unik secara otomatis dengan format `SD-YYYY-XXXX` (contoh: `SD-2026-0012`)[cite: 6, 8].
- **Format Nomor Surat**: Penomoran dokumen cetak (Pemberitahuan User & Laporan Bupati) menggunakan format otomatis: `300/[NOMOR_OTOMATIS]/V.06-WK/YYYY`[cite: 12, 13].

### 11. Alur Penanganan & Target Waktu (SLA)

Berdasarkan SOP Pelaporan SI-DENI[cite: 14], sistem diharapkan dapat mengakomodasi dan melacak rentang waktu pemrosesan laporan dengan tahapan baku berikut:

1. **Input Laporan Awal (Aparatur Kampung)**: Menerima informasi dari masyarakat, meninjau lokasi, dan menginput laporan ke aplikasi (Target waktu: 1 Jam)[cite: 14].
2. **Pengecekan Awal (Admin Kesbangpol)**: Memeriksa laporan masuk beserta kelengkapan foto dan titik koordinat (Target waktu: 10 Menit)[cite: 14].
3. **Validasi & Konfirmasi (Admin Kesbangpol)**: Memvalidasi kebenaran kejadian dengan menghubungi Aparatur Kampung terkait (Target waktu: 30 Menit)[cite: 14].
4. **Penerbitan Disposisi (Admin Kesbangpol)**: Mencetak lembar disposisi digital dan meneruskannya secara hierarki kepada Kepala Badan (Target waktu: 15 Menit)[cite: 14].
5. **Instruksi Penanganan (Kepala Badan)**: Menelaah disposisi dan menginstruksikan koordinasi lapangan kepada Camat/Forkopimcam (Target waktu: 1 Jam)[cite: 14].
6. **Tindak Lanjut Lapangan (Camat/Forkopimcam)**: Turun ke lapangan untuk melakukan langkah persuasif, mediasi, atau pengamanan (Target waktu: 1-2 Hari)[cite: 14].
7. **Laporan Akhir (Kepala Badan ke Bupati)**: Menyusun laporan resmi hasil penanganan untuk dilaporkan kepada Bupati (Target waktu: 1 Hari setelah penanganan selesai)[cite: 14].
   _(Catatan Developer: Sangat disarankan untuk membuat fitur indikator waktu peringatan pada tabel laporan Admin guna memastikan pemrosesan tidak melewati batas SLA yang ditentukan)._

---

## 👥 Hak Akses & Peran (_Roles_)

| Peran                 | Deskripsi Hak Akses                                                                                                                                     |
| :-------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Aparatur Kampung**  | Membuat laporan baru, mengunggah foto bukti, melengkapi data jika ada status "Perlu Klarifikasi", melihat status akhir laporannya sendiri.              |
| **Admin Kesbangpol**  | Membaca semua laporan masuk, memvalidasi kelengkapan, mengubah status laporan, mencetak lembar disposisi, mengelola data master (user, role, kategori). |
| **Kepala Badan**      | Melihat rekapitulasi data, memberikan catatan/disposisi, mencetak Laporan Deteksi Dini untuk Bupati.                                                    |
| **Camat/Forkopimcam** | Menerima instruksi tindak lanjut dari Kesbangpol, melaporkan/mengupdate hasil penanganan (mediasi) dari lapangan.                                       |

---

## 🛠 Kebutuhan Sistem & Teknologi

- **Bahasa Pemrograman**: PHP (>= 8.4)
- **Framework Backend**: Laravel (>= 10.x / 13.x)
- **Database**: MySQL (>= 8.0) atau PostgreSQL (Direkomendasikan untuk data geospasial)
- **Backend Packages**:
    - `spatie/laravel-permission`: Role & Permission
    - `yajra/laravel-datatables`: DataTables Server-Side Processing
    - `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf`: PDF Generator
- **Frontend/UI**: Bootstrap / Tailwind CSS, Blade Templating
- **Frontend Libraries**:
    - **Chart.js**: Visualisasi data analitik dan grafik di _dashboard_.
    - **Leaflet.js**: Peta _dashboard_ (sebaran laporan) & form input _geotagging_.
    - **DataTables**: Grid tabel dengan fitur _pagination_, _search_, & _sort_.
    - **Select2**: Pencarian form select untuk Master Data.
    - **SweetAlert2**: Notifikasi alert & konfirmasi.
    - **Dropzone.js**: Upload file _drag-and-drop_ & _real-time_.

---

## ⚙️ Panduan Instalasi (Development)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan pengembangan lokal:

1. **Clone repositori**
    ```bash
    git clone [https://github.com/username/sideni-waykanan.git](https://github.com/username/sideni-waykanan.git)
    cd sideni-waykanan
    ```
