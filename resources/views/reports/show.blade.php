@extends('layouts.app', ['title' => $report->registration_number.' | SI-DENI'])

@section('page-title', 'Detail Laporan')
@section('page-subtitle', $report->registration_number)

@php
    $canViewIdentity = $report->identityVisibleTo(auth()->user());
    $statusClasses = \App\Support\ReportBadges::statusClasses($report->status->value);
    $riskClasses = \App\Support\ReportBadges::riskClasses($report->risk_level->value);
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="w-full space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('reports.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar laporan</a>

        <section class="glass p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-700">Nomor Registrasi</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-800">{{ $report->registration_number }}</h1>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="badge {{ $statusClasses }}">{{ str($report->status->value)->replace('_', ' ')->title() }}</span>
                        <span class="badge {{ $riskClasses }}">Risiko {{ str($report->risk_level->value)->title() }}</span>
                        <span class="badge bg-white/70 text-slate-600"><x-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $report->village?->name ?? '-' }} — {{ $report->village?->district?->name ?? '-' }}</span>
                        <span class="badge bg-white/70 text-slate-600"><x-icon name="document" class="h-3.5 w-3.5" /> {{ $report->reported_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if (in_array($report->status, [\App\Enums\ReportStatus::Verified, \App\Enums\ReportStatus::InProgress, \App\Enums\ReportStatus::Completed], true))
                        @can('export reports')
                            <x-button variant="ghost" :href="route('reports.pdf.bupati', $report)" icon="document">Laporan Bupati</x-button>
                        @endcan
                        @if ($report->submitted_by_id === auth()->id() || auth()->user()->can('verify reports') || auth()->user()->can('issue dispositions'))
                            <x-button variant="ghost" :href="route('reports.pdf.notice', $report)" icon="clipboard">Surat Pemberitahuan</x-button>
                        @endif
                    @else
                        <span class="badge bg-white/70 text-slate-500"><x-icon name="shield" class="h-3.5 w-3.5" /> Dokumen cetak tersedia setelah laporan diverifikasi</span>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-panel title="Informasi Laporan">
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Kategori kerawanan</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->reportCategory?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Jenis kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->incidentType?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Kampung / Kecamatan</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->village?->name ?? '-' }} / {{ $report->village?->district?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Lokasi kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->location_name }}</dd></div>
                        <div><dt class="text-slate-500">Waktu kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->occurred_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Dilaporkan oleh</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->submittedBy?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Batas waktu tahap ini</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->slaDeadline()?->format('d/m/Y H:i') ?? '-' }}@if ($report->slaState() === 'overdue') <span class="text-rose-600">(terlambat)</span>@elseif ($report->slaState() === 'warning') <span class="text-amber-600">(mendekati batas)</span>@endif</dd></div>
                        <div><dt class="text-slate-500">Koordinat</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->latitude }}, {{ $report->longitude }}</dd></div>
                    </dl>
                </x-panel>

                <x-panel title="Identitas Pelapor" subtitle="Data ini hanya untuk verifikasi awal.">
                    <div class="glass-soft p-4 text-sm">
                        @if ($canViewIdentity)
                            <p>Nama: <span class="font-medium text-slate-800">{{ $report->reporter_name }}</span></p>
                            <p class="mt-1">No. telepon: <span class="font-medium text-slate-800">{{ $report->reporter_phone ?? '-' }}</span></p>
                        @else
                            <p>Nama: <span class="font-medium text-slate-800">{{ $report->maskedReporterName() }}</span></p>
                            <p class="mt-1">No. telepon: <span class="font-medium text-slate-800">{{ $report->maskedReporterPhone() ?? '-' }}</span></p>
                            <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500"><x-icon name="shield" class="h-3.5 w-3.5" /> Identitas disamarkan. Hanya petugas berwenang yang dapat melihat data lengkap.</p>
                        @endif
                    </div>
                </x-panel>

                <x-panel title="Uraian Kejadian">
                    <p class="whitespace-pre-wrap leading-relaxed text-slate-700">{{ $report->description }}</p>
                </x-panel>

                <x-panel title="Bukti / Lampiran">
                    @if ($report->attachments->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($report->attachments as $attachment)
                                <a class="glass-soft flex items-center gap-3 p-3 transition hover:bg-white/80" href="{{ route('reports.attachments.show', [$report, $attachment]) }}">
                                    @if (str_starts_with((string) $attachment->mime_type, 'image/'))
                                        <img class="h-14 w-14 rounded-lg object-cover" src="{{ route('reports.attachments.show', [$report, $attachment]) }}" alt="{{ $attachment->original_name }}" loading="lazy">
                                    @else
                                        <span class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><x-icon name="document" class="h-6 w-6" /></span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-slate-700">{{ $attachment->original_name }}</span>
                                        <span class="block text-xs text-slate-500">{{ number_format($attachment->size / 1024, 0) }} KB</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Tidak ada lampiran.</p>
                    @endif
                </x-panel>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <x-panel title="Titik Lokasi">
                    <div id="report-map" class="h-56 rounded-xl border border-white/50"></div>
                </x-panel>

                <x-panel title="Riwayat Status">
                    <ol class="relative grid gap-4 border-l border-white/60 pl-5">
                        @forelse ($report->statusHistories->sortBy('created_at') as $history)
                            <li class="relative">
                                <span class="absolute -left-[26px] top-1 h-3 w-3 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 ring-4 ring-white/70"></span>
                                <p class="text-sm font-medium text-slate-800">{{ str($history->to_status->value)->replace('_', ' ')->title() }}</p>
                                <p class="text-xs text-slate-500">{{ $history->created_at?->format('d/m/Y H:i') }} — {{ $history->changedBy?->name ?? 'Sistem' }}</p>
                                @if ($history->note)<p class="mt-1 text-sm text-slate-600">{{ $history->note }}</p>@endif
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Belum ada riwayat.</li>
                        @endforelse
                    </ol>
                </x-panel>
            </aside>
        </div>

        @if ($report->submitted_by_id === auth()->id() && $report->status === \App\Enums\ReportStatus::NeedsClarification)
            <section class="glass border-amber-200/60 bg-amber-50/70 p-6">
                <h2 class="text-lg font-bold text-amber-900">Laporan perlu dilengkapi</h2>
                <p class="mt-1 text-sm text-amber-800">Perbaiki data sesuai catatan petugas lalu kirim ulang.</p>
                <x-button class="mt-3" :href="route('reports.edit', $report)">Perbaiki laporan</x-button>
            </section>
        @endif

        @if (auth()->user()->can('verify reports') && $report->status === \App\Enums\ReportStatus::Pending)
            <section class="glass flex flex-wrap items-center justify-between gap-3 border-amber-200/60 bg-amber-50/70 p-4 text-sm text-amber-800">
                <span class="flex items-center gap-2"><x-icon name="shield" class="h-4 w-4" /> Laporan ini menunggu verifikasi kelengkapan data.</span>
                <x-button :href="route('reports.verify.show', $report)">Buka halaman verifikasi</x-button>
            </section>
        @endif

        @if ($report->follow_up || $report->recommendation)
            <x-panel title="Tindak Lanjut & Saran Tindakan">
                <div class="grid gap-4 text-sm">
                    @if ($report->follow_up)
                        <div>
                            <p class="text-slate-500">Tindak Lanjut</p>
                            <p class="mt-1 whitespace-pre-wrap text-slate-700">{{ $report->follow_up }}</p>
                        </div>
                    @endif
                    @if ($report->recommendation)
                        <div>
                            <p class="text-slate-500">Saran Tindakan</p>
                            <p class="mt-1 whitespace-pre-wrap text-slate-700">{{ $report->recommendation }}</p>
                        </div>
                    @endif
                </div>
            </x-panel>
        @endif

        @if (auth()->user()->can('verify reports') && $report->status === \App\Enums\ReportStatus::Verified)
            <x-panel title="Tandai Selesai" subtitle="Tandai laporan sebagai selesai / tertangani.">
                <form class="flex flex-wrap items-end gap-3" method="POST" action="{{ route('reports.complete', $report) }}">
                    @csrf
                    <input class="field max-w-md" name="note" placeholder="Catatan penyelesaian (opsional)" maxlength="5000">
                    <x-button type="submit">Tandai selesai</x-button>
                </form>
            </x-panel>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const lat = {{ (float) $report->latitude }};
            const lng = {{ (float) $report->longitude }};
            const map = L.map('report-map', { scrollWheelZoom: false }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
            L.circleMarker([lat, lng], { radius: 9, color: '#0284c7', fillColor: '#0ea5e9', fillOpacity: 0.7, weight: 2 })
                .addTo(map)
                .bindPopup(@json($report->location_name));
        })();
    </script>
@endpush
