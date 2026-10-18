@extends('layouts.app', ['title' => 'Export Laporan | SI-DENI'])

@section('page-title', 'Export Laporan')
@section('page-subtitle', 'Unduh laporan sesuai filter wilayah, jenis, dan periode')

@php
    $regionLabel = match ($scope) {
        'village' => ($districts->first()?->name ?? '-').' — '.($villages->first()?->name ?? '-'),
        'district' => $districts->first()?->name ?? '-',
        default => null,
    };
@endphp

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('reports.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar laporan</a>

        @if ($scope === 'none')
            <div class="glass flex items-start gap-3 border-rose-200/60 bg-rose-50/70 p-4 text-sm text-rose-800">
                <x-icon name="shield" class="h-5 w-5 shrink-0 text-rose-600" />
                <span>Akun Anda belum ditetapkan wilayah (kecamatan atau kampung), sehingga belum dapat mengekspor laporan. Hubungi administrator untuk penetapan wilayah.</span>
            </div>
        @elseif ($scope !== 'all')
            <div class="glass flex items-start gap-3 border-amber-200/60 bg-amber-50/70 p-4 text-sm text-amber-800">
                <x-icon name="map-pin" class="h-5 w-5 shrink-0 text-amber-600" />
                <span>Export dibatasi ke wilayah Anda: <strong>{{ $regionLabel }}</strong>. Hubungi administrator bila cakupan perlu diperluas.</span>
            </div>
        @endif

        <x-panel title="Filter Export" subtitle="Kosongkan filter untuk mengekspor seluruh laporan yang menjadi kewenangan Anda.">
            <form class="grid gap-5" method="GET" action="{{ route('reports.export.download') }}">
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="label">Kecamatan
                        <select class="select2 field" name="district_id" @disabled($scope !== 'all')>
                            @if ($scope === 'all')
                                <option value="">Semua kecamatan</option>
                            @endif
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}" @selected($scope !== 'all' || (string) request('district_id') === (string) $district->id)>{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="label">Kampung
                        <select class="select2 field" name="village_id" @disabled($scope === 'village')>
                            @if ($scope !== 'village')
                                <option value="">Semua kampung</option>
                            @endif
                            @foreach ($villages as $village)
                                <option value="{{ $village->id }}" @selected($scope === 'village' || (string) request('village_id') === (string) $village->id)>{{ $village->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="label">Kategori kerawanan
                        <select class="select2 field" name="report_category_id">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('report_category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="label">Jenis kejadian
                        <select class="select2 field" name="incident_type_id">
                            <option value="">Semua jenis kejadian</option>
                            @foreach ($incidentTypes->groupBy(fn ($type) => $type->reportCategory?->name ?? 'Lainnya') as $categoryName => $types)
                                <optgroup label="{{ $categoryName }}">
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" @selected((string) request('incident_type_id') === (string) $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </label>
                    <label class="label">Status
                        <select class="select2 field" name="status">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ str($status->value)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="label">Dari tanggal <span class="text-xs font-normal text-slate-500">(tanggal laporan dibuat)</span>
                        <input class="field" type="text" data-date name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </label>
                    <label class="label">Sampai tanggal
                        <input class="field" type="text" data-date name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </label>
                </div>

                <fieldset class="grid gap-2">
                    <legend class="text-sm font-medium text-slate-700">Format berkas</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="radio" name="format" value="xlsx" @checked(request('format', 'xlsx') === 'xlsx')> Excel (.xlsx)</label>
                        <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="radio" name="format" value="csv" @checked(request('format') === 'csv')> CSV (.csv)</label>
                    </div>
                </fieldset>

                <div class="flex flex-wrap justify-end gap-3">
                    <x-button variant="ghost" :href="route('reports.index')">Batal</x-button>
                    <x-button type="submit" icon="download" :disabled="$scope === 'none'">Unduh laporan</x-button>
                </div>
            </form>
        </x-panel>

        <x-panel title="Keterangan">
            <ul class="grid gap-3 text-sm text-slate-600">
                <li class="flex gap-2"><x-icon name="document" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Kolom yang diekspor mencakup nomor, wilayah, klasifikasi, waktu, lokasi, uraian, tindak lanjut, dan saran tindakan.</li>
                <li class="flex gap-2"><x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Identitas pelapor (nama &amp; nomor telepon) hanya tampil penuh bila Anda berhak melihatnya; jika tidak, disamarkan.</li>
                <li class="flex gap-2"><x-icon name="chart" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" /> Berkas CSV memakai pemisah koma dan dapat dibuka langsung di Excel.</li>
            </ul>
        </x-panel>
    </div>
@endsection
