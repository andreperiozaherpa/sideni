@extends('layouts.app', ['title' => 'Verifikasi '.$report->registration_number.' | SI-DENI'])

@section('page-title', 'Verifikasi Laporan')
@section('page-subtitle', $report->registration_number)

@php
    $canViewIdentity = $report->identityVisibleTo(auth()->user());
    $statusClasses = match ($report->status->value) {
        'pending' => 'bg-amber-100 text-amber-800',
        'needs_clarification' => 'bg-orange-100 text-orange-800',
        'verified' => 'bg-sky-100 text-sky-800',
        'in_progress' => 'bg-indigo-100 text-indigo-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-rose-100 text-rose-800',
        default => 'bg-slate-100 text-slate-700',
    };
    $riskClasses = match ($report->risk_level->value) {
        'low' => 'bg-emerald-100 text-emerald-800',
        'medium' => 'bg-amber-100 text-amber-800',
        'high' => 'bg-rose-100 text-rose-800',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

@section('content')
    <div class="w-full space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('reports.verify.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar verifikasi</a>
            <a class="link text-sm" href="{{ route('reports.show', $report) }}">Lihat detail lengkap</a>
        </div>

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
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-panel title="Ringkasan Laporan">
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Kategori kerawanan</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->reportCategory?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Jenis kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->incidentType?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Kampung / Kecamatan</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->village?->name ?? '-' }} / {{ $report->village?->district?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Lokasi kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->location_name }}</dd></div>
                        <div><dt class="text-slate-500">Waktu kejadian</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->occurred_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Koordinat</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->latitude }}, {{ $report->longitude }}</dd></div>
                        <div><dt class="text-slate-500">Dilaporkan oleh</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->submittedBy?->name ?? '-' }}</dd></div>
                        <div><dt class="text-slate-500">Batas waktu tahap ini</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $report->slaDeadline()?->format('d/m/Y H:i') ?? '-' }}@if ($report->slaState() === 'overdue') <span class="text-rose-600">(terlambat)</span>@elseif ($report->slaState() === 'warning') <span class="text-amber-600">(mendekati batas)</span>@endif</dd></div>
                    </dl>
                </x-panel>

                <x-panel title="Identitas Pelapor" subtitle="Hanya untuk verifikasi awal.">
                    <div class="glass-soft p-4 text-sm">
                        @if ($canViewIdentity)
                            <p>Nama: <span class="font-medium text-slate-800">{{ $report->reporter_name }}</span></p>
                            <p class="mt-1">No. telepon: <span class="font-medium text-slate-800">{{ $report->reporter_phone ?? '-' }}</span></p>
                        @else
                            <p>Nama: <span class="font-medium text-slate-800">{{ $report->maskedReporterName() }}</span></p>
                            <p class="mt-1">No. telepon: <span class="font-medium text-slate-800">{{ $report->maskedReporterPhone() ?? '-' }}</span></p>
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
                <x-panel title="Keputusan Verifikasi" subtitle="Periksa kelengkapan sebelum memutuskan.">
                    <div class="grid gap-3">
                        <form class="glass-soft grid gap-3 p-4" method="POST" action="{{ route('reports.verify', $report) }}">
                            @csrf
                            <p class="text-sm font-medium text-slate-700">Data lengkap &amp; valid</p>
                            <label class="label">Tindak Lanjut
                                <textarea class="field" name="follow_up" rows="3" maxlength="5000" placeholder="Tindak lanjut yang dilakukan…">{{ old('follow_up', $report->follow_up) }}</textarea>
                            </label>
                            <label class="label">Saran Tindakan
                                <textarea class="field" name="recommendation" rows="3" maxlength="5000" placeholder="Saran tindakan / kebijakan…">{{ old('recommendation', $report->recommendation) }}</textarea>
                            </label>
                            <x-button type="submit" class="w-full">Verifikasi</x-button>
                        </form>
                        <form class="glass-soft grid gap-3 p-4" method="POST" action="{{ route('reports.clarify', $report) }}">
                            @csrf
                            <p class="text-sm font-medium text-slate-700">Kembalikan ke kampung</p>
                            <input class="field" name="note" placeholder="Catatan klarifikasi" maxlength="5000">
                            <x-button type="submit" variant="ghost" class="w-full">Perlu klarifikasi</x-button>
                        </form>
                        <form class="glass-soft grid gap-3 p-4" method="POST" action="{{ route('reports.reject', $report) }}">
                            @csrf
                            <p class="text-sm font-medium text-slate-700">Laporan tidak valid</p>
                            <input class="field" name="note" placeholder="Alasan penolakan" maxlength="5000" required>
                            <x-button type="submit" variant="danger" class="w-full">Tolak</x-button>
                        </form>
                    </div>
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
    </div>
@endsection
