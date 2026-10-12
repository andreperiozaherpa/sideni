@extends('layouts.app', ['title' => 'Dashboard | SI-DENI Way Kanan'])

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan dan analitik deteksi dini')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="space-y-6">
        <section class="glass flex flex-wrap items-center justify-between gap-4 p-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Selamat datang, {{ auth()->user()->name }}</h2>
                <p class="mt-1 text-sm text-slate-600">Akses Anda disesuaikan dengan peran dan hak akses yang ditetapkan.</p>
            </div>
            <div class="flex gap-3">
                <span class="badge bg-sky-100 text-sky-700">{{ auth()->user()->getRoleNames()->join(', ') ?: 'Tanpa role' }}</span>
                <span class="badge bg-indigo-100 text-indigo-700">{{ auth()->user()->getAllPermissions()->count() }} hak akses</span>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="glass p-5"><p class="text-sm text-slate-500">Total laporan</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p></div>
            <div class="glass p-5"><p class="text-sm text-slate-500">Menunggu</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['pending'] }}</p></div>
            <div class="glass p-5"><p class="text-sm text-slate-500">Dalam penanganan</p><p class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['inProgress'] }}</p></div>
            <div class="glass p-5"><p class="text-sm text-slate-500">Selesai</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['completed'] }}</p></div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <x-panel title="Laporan per Status"><div class="h-64"><canvas id="status-chart"></canvas></div></x-panel>
            <x-panel title="Laporan per Bulan"><div class="h-64"><canvas id="monthly-chart"></canvas></div></x-panel>
            <x-panel title="Laporan per Tingkat Risiko"><div class="h-64"><canvas id="risk-chart"></canvas></div></x-panel>
            <x-panel title="Laporan per Kategori"><div class="h-64"><canvas id="category-chart"></canvas></div></x-panel>
        </section>

        <x-panel title="Peta Sebaran Potensi Konflik">
            <div id="report-map" class="h-96 rounded-xl border border-white/50"></div>
        </x-panel>

        <x-panel title="Laporan Terbaru">
            <x-slot:actions><a class="link text-sm" href="{{ route('reports.index') }}">Lihat semua</a></x-slot:actions>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead><tr><th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor</th><th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Kampung</th><th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</th><th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th></tr></thead>
                    <tbody class="divide-y divide-white/50">
                        @forelse ($recentReports as $report)
                            <tr>
                                <td class="px-4 py-3 font-medium"><a class="link" href="{{ route('reports.show', $report) }}">{{ $report->registration_number }}</a></td>
                                <td class="px-4 py-3">{{ $report->village?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $report->reportCategory?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ str($report->status->value)->replace('_', ' ')->title() }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-3 text-slate-500" colspan="4">Belum ada laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

        <section class="grid gap-4 md:grid-cols-3">
            <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('reports.index') }}"><x-icon name="document" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Laporan &amp; Penugasan</h3><p class="mt-1 text-sm text-slate-600">Lihat dan kelola laporan deteksi dini.</p></a>
            @can('create reports')
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('reports.create') }}"><x-icon name="plus" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Buat Laporan</h3><p class="mt-1 text-sm text-slate-600">Kirim laporan potensi konflik baru.</p></a>
            @endcan
            @can('manage master data')
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('report-categories.index') }}"><x-icon name="tag" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Kategori Kerawanan</h3><p class="mt-1 text-sm text-slate-600">Kelola klasifikasi kerawanan.</p></a>
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('incident-types.index') }}"><x-icon name="clipboard" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Jenis Kejadian</h3><p class="mt-1 text-sm text-slate-600">Kelola jenis kejadian per kategori.</p></a>
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('districts.index') }}"><x-icon name="building" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Kecamatan</h3><p class="mt-1 text-sm text-slate-600">Kelola data kecamatan.</p></a>
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('villages.index') }}"><x-icon name="map-pin" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Kampung</h3><p class="mt-1 text-sm text-slate-600">Kelola data kampung.</p></a>
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('settings.edit') }}"><x-icon name="cog" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Pengaturan</h3><p class="mt-1 text-sm text-slate-600">Identitas instansi dan pejabat.</p></a>
            @endcan
            @can('manage users')
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('users.index') }}"><x-icon name="users" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Manajemen Pengguna</h3><p class="mt-1 text-sm text-slate-600">Kelola akun dan penugasan role.</p></a>
            @endcan
            @can('manage roles')
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('roles.index') }}"><x-icon name="shield" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Manajemen Role</h3><p class="mt-1 text-sm text-slate-600">Atur role dan hak akses.</p></a>
            @endcan
            @can('manage permissions')
                <a class="glass p-5 transition hover:-translate-y-0.5" href="{{ route('permissions.index') }}"><x-icon name="key" class="h-6 w-6 text-sky-600" /><h3 class="mt-3 font-bold text-slate-800">Manajemen Hak Akses</h3><p class="mt-1 text-sm text-slate-600">Kelola daftar permission dinamis.</p></a>
            @endcan
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <script>
        const statusChart = @json($statusChart);
        const monthlyChart = @json($monthlyChart);
        const riskChart = @json($riskChart);
        const categoryChart = @json($categoryChart);
        const mapPoints = @json($mapPoints);
        const statusColors = {
            pending: '#f59e0b',
            needs_clarification: '#f97316',
            verified: '#0ea5e9',
            in_progress: '#6366f1',
            completed: '#16a34a',
            rejected: '#dc2626',
        };

        if (window.Chart) {
            new Chart(document.getElementById('status-chart'), {
                type: 'doughnut',
                data: { labels: statusChart.labels, datasets: [{ data: statusChart.values, backgroundColor: ['#f59e0b', '#f97316', '#0ea5e9', '#6366f1', '#16a34a', '#dc2626'] }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
            });
            new Chart(document.getElementById('monthly-chart'), {
                type: 'line',
                data: { labels: monthlyChart.labels, datasets: [{ label: 'Laporan', data: monthlyChart.values, borderColor: '#0369a1', backgroundColor: 'rgba(3,105,161,0.15)', fill: true, tension: 0.3 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
            new Chart(document.getElementById('risk-chart'), {
                type: 'doughnut',
                data: { labels: riskChart.labels, datasets: [{ data: riskChart.values, backgroundColor: ['#16a34a', '#f59e0b', '#dc2626'] }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
            });
            new Chart(document.getElementById('category-chart'), {
                type: 'bar',
                data: { labels: categoryChart.labels, datasets: [{ label: 'Laporan', data: categoryChart.values, backgroundColor: '#0369a1' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }

        if (window.L) {
            const map = L.map('report-map').setView([-4.44705, 104.52753], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

            const riskWeight = { low: 0.35, medium: 0.65, high: 1 };
            const markers = L.layerGroup();
            const heatData = [];

            mapPoints.forEach((point) => {
                const color = statusColors[point.status] || '#64748b';
                L.circleMarker([point.lat, point.lng], { radius: 7, color, fillColor: color, fillOpacity: 0.6, weight: 2 })
                    .bindPopup(`<strong>${point.number}</strong><br>${point.village ?? '-'} — ${point.location}`)
                    .addTo(markers);
                heatData.push([point.lat, point.lng, riskWeight[point.risk] ?? 0.5]);
            });

            markers.addTo(map);

            const layers = { 'Titik laporan': markers };

            if (window.L.heatLayer) {
                layers['Kepadatan (heatmap)'] = L.heatLayer(heatData, { radius: 28, blur: 22, maxZoom: 12, minOpacity: 0.35 });
            }

            L.control.layers(null, layers, { collapsed: false }).addTo(map);
        }
    </script>
@endpush
