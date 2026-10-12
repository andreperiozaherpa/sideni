@extends('layouts.app', ['title' => ($report->exists ? 'Perbaiki' : 'Buat').' Laporan | SI-DENI'])

@section('page-title', $report->exists ? 'Perbaiki Laporan' : 'Buat Laporan')
@section('page-subtitle', 'Form pelaporan deteksi dini potensi konflik')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="w-full space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('reports.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar laporan</a>
            <span class="badge bg-white/70 text-slate-600">{{ $report->exists ? 'Mode perbaikan' : 'Laporan baru' }}</span>
        </div>

        @if ($report->exists)
            <div class="glass flex items-start gap-3 border-amber-200/60 bg-amber-50/70 p-4 text-sm text-amber-800">
                <x-icon name="clipboard" class="h-5 w-5 shrink-0 text-amber-600" />
                <span>Laporan ini berstatus <strong>Perlu Klarifikasi</strong>. Lengkapi data sesuai catatan petugas, lalu kirim ulang untuk diverifikasi kembali.</span>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $report->exists ? route('reports.update', $report) : route('reports.store') }}">
            @csrf @if ($report->exists) @method('PUT') @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-panel>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-sky-500/25">1</span>
                            <div><h2 class="font-semibold text-slate-800">Data Pelapor</h2><p class="text-xs text-slate-500">Identitas masyarakat pelapor — bersifat rahasia.</p></div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="label">Nama pelapor
                                <input class="field" name="reporter_name" value="{{ old('reporter_name', $report->reporter_name) }}" required>
                            </label>
                            <label class="label">Nomor telepon pelapor <span class="text-xs font-normal text-slate-500">(opsional)</span>
                                <input class="field" name="reporter_phone" value="{{ old('reporter_phone', $report->reporter_phone) }}" inputmode="tel" maxlength="20" placeholder="08xx xxxx xxxx">
                            </label>
                        </div>
                    </x-panel>

                    <x-panel>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-sky-500/25">2</span>
                            <div><h2 class="font-semibold text-slate-800">Waktu &amp; Lokasi</h2><p class="text-xs text-slate-500">Kapan dan di mana kejadian berlangsung.</p></div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="label">Waktu dilaporkan
                                <input class="field" type="text" data-datetime name="reported_at" value="{{ old('reported_at', $report->reported_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i')) }}" placeholder="YYYY-MM-DD HH:MM" autocomplete="off" required>
                            </label>
                            <label class="label">Waktu kejadian <span class="text-xs font-normal text-slate-500">(opsional)</span>
                                <input class="field" type="text" data-datetime name="occurred_at" value="{{ old('occurred_at', $report->occurred_at?->format('Y-m-d H:i')) }}" placeholder="YYYY-MM-DD HH:MM" autocomplete="off">
                            </label>
                        </div>
                        <div class="mt-5 grid gap-5">
                            <label class="label">Kampung
                                <select class="select2 field" name="village_id" required>
                                    <option value="">Pilih kampung</option>
                                    @foreach ($villages as $village)<option value="{{ $village->id }}" @selected(old('village_id', $report->village_id) == $village->id)>{{ $village->name }}</option>@endforeach
                                </select>
                            </label>
                            <label class="label">Nama lokasi kejadian
                                <input class="field" name="location_name" value="{{ old('location_name', $report->location_name) }}" placeholder="Contoh: Jalan Lintas Blambangan Umpu KM 5" required>
                            </label>
                        </div>
                    </x-panel>

                    <x-panel>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-sky-500/25">3</span>
                            <div><h2 class="font-semibold text-slate-800">Klasifikasi Isu</h2><p class="text-xs text-slate-500">Kategori kerawanan, jenis kejadian, dan tingkat risiko.</p></div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="label">Kategori kerawanan
                                <select class="select2 field" id="category" name="report_category_id" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('report_category_id', $report->report_category_id) == $category->id)>{{ $category->name }}</option>@endforeach
                                </select>
                            </label>
                            <label class="label">Jenis kejadian
                                <select class="select2 field" id="incident-type" name="incident_type_id">
                                    <option value="">Pilih jenis kejadian</option>
                                </select>
                            </label>
                        </div>
                        <div class="mt-5">
                            <label class="label">Tingkat risiko
                                <select class="select2 field" name="risk_level" required>
                                    <option value="low" @selected(old('risk_level', $report->risk_level?->value) === 'low')>Rendah</option>
                                    <option value="medium" @selected(old('risk_level', $report->risk_level?->value) === 'medium')>Sedang</option>
                                    <option value="high" @selected(old('risk_level', $report->risk_level?->value) === 'high')>Tinggi</option>
                                </select>
                            </label>
                        </div>
                    </x-panel>

                    <x-panel>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-sky-500/25">4</span>
                            <div><h2 class="font-semibold text-slate-800">Uraian Kejadian</h2><p class="text-xs text-slate-500">Jelaskan kronologi secara faktual dan tidak menghakimi.</p></div>
                        </div>
                        <label class="label">
                            <textarea class="field" name="description" rows="5" placeholder="Tuliskan kronologi kejadian, pihak yang terlibat, dan potensi eskalasi…" required>{{ old('description', $report->description) }}</textarea>
                        </label>
                    </x-panel>

                    <x-panel>
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-sky-500/25">5</span>
                            <div><h2 class="font-semibold text-slate-800">Bukti / Lampiran</h2><p class="text-xs text-slate-500">jpg/png/pdf, maks. 20 MB/berkas — foto otomatis dikompres &lt; 500 KB.</p></div>
                        </div>
                        <div class="dropzone" data-dropzone="{{ route('uploads.temporary.store') }}" data-accept="image/jpeg,image/png,application/pdf"></div>
                        <div data-dropzone-input></div>

                        @if ($report->exists && $report->attachments->isNotEmpty())
                            <div class="glass-soft mt-4 p-4 text-sm">
                                <p class="font-medium text-slate-700">Lampiran tersimpan</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($report->attachments as $attachment)
                                        <li><a class="link" href="{{ route('reports.attachments.show', [$report, $attachment]) }}">{{ $attachment->original_name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </x-panel>

                    <div class="flex flex-wrap justify-end gap-3">
                        <x-button variant="ghost" :href="route('reports.index')">Batal</x-button>
                        <x-button type="submit">{{ $report->exists ? 'Kirim ulang laporan' : 'Kirim laporan' }}</x-button>
                    </div>
                </div>

                <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                    <x-panel title="Titik Koordinat" subtitle="Klik peta atau gunakan GPS.">
                        <div id="map" class="h-64 rounded-xl border border-white/50"></div>
                        <x-button type="button" variant="ghost" id="gps" class="mt-3 w-full" icon="map-pin">Ambil lokasi GPS</x-button>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <label class="label">Latitude<input class="field" id="lat" name="latitude" value="{{ old('latitude', $report->latitude) }}" required></label>
                            <label class="label">Longitude<input class="field" id="lng" name="longitude" value="{{ old('longitude', $report->longitude) }}" required></label>
                        </div>
                    </x-panel>

                    <x-panel title="Panduan Pengisian">
                        <ul class="grid gap-3 text-sm text-slate-600">
                            <li class="flex gap-2"><x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Isi data sesuai kejadian sebenarnya — cepat, faktual, dan berbasis bukti.</li>
                            <li class="flex gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Titik koordinat wajib diisi untuk pemetaan sebaran konflik.</li>
                            <li class="flex gap-2"><x-icon name="document" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Lampirkan foto/dokumen pendukung sebagai bukti.</li>
                            <li class="flex gap-2"><x-icon name="users" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Identitas pelapor bersifat rahasia dan hanya dilihat petugas berwenang.</li>
                        </ul>
                    </x-panel>
                </aside>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const categorySelect = document.querySelector('#category');
        const incidentSelect = document.querySelector('#incident-type');
        const latInput = document.querySelector('#lat');
        const lngInput = document.querySelector('#lng');
        const selectedIncident = @json(old('incident_type_id', $report->incident_type_id));
        const initialLat = parseFloat(latInput.value) || -4.48;
        const initialLng = parseFloat(lngInput.value) || 104.52;
        const map = L.map('map').setView([initialLat, initialLng], latInput.value ? 15 : 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
        let marker;

        function setPoint(point) {
            latInput.value = point.lat;
            lngInput.value = point.lng;
            if (marker) { marker.remove(); }
            marker = L.marker(point).addTo(map);
            map.setView(point, 15);
        }

        if (latInput.value && lngInput.value) { setPoint({ lat: initialLat, lng: initialLng }); }
        map.on('click', (event) => setPoint(event.latlng));
        document.querySelector('#gps').addEventListener('click', () => navigator.geolocation.getCurrentPosition((position) => setPoint({ lat: position.coords.latitude, lng: position.coords.longitude })));

        async function loadIncidentTypes() {
            incidentSelect.innerHTML = '<option value="">Pilih jenis kejadian</option>';
            if (!categorySelect.value) {
                window.refreshSelect2?.(incidentSelect);
                return;
            }
            const response = await fetch(`/report-categories/${categorySelect.value}/incident-types`);
            (await response.json()).forEach((type) => {
                const option = new Option(type.name, type.id);
                option.selected = String(type.id) === String(selectedIncident);
                incidentSelect.add(option);
            });
            window.refreshSelect2?.(incidentSelect);
        }

        categorySelect.addEventListener('change', loadIncidentTypes);
        if (window.jQuery) {
            window.jQuery(categorySelect).on('select2:select select2:clear', loadIncidentTypes);
        }
        loadIncidentTypes();
    </script>
@endpush
