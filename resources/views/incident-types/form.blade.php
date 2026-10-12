@extends('layouts.app', ['title' => 'Jenis Kejadian | SI-DENI'])

@section('page-title', ($incidentType->exists ? 'Ubah' : 'Tambah').' Jenis Kejadian')
@section('page-subtitle', 'Data referensi jenis kejadian')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('incident-types.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $incidentType->exists ? 'Ubah' : 'Tambah' }} jenis kejadian">
            <form class="grid gap-5" method="POST" action="{{ $incidentType->exists ? route('incident-types.update', $incidentType) : route('incident-types.store') }}">
                @csrf @if ($incidentType->exists) @method('PUT') @endif
                <label class="label">Kategori
                    <select class="select2 field" name="report_category_id" required>
                        @foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('report_category_id', $incidentType->report_category_id) == $category->id)>{{ $category->name }}</option>@endforeach
                    </select>
                </label>
                <label class="label">Kode<input class="field" name="code" value="{{ old('code', $incidentType->code) }}" required></label>
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $incidentType->name) }}" required></label>
                <label class="label">Urutan<input class="field" type="number" min="0" name="sort_order" value="{{ old('sort_order', $incidentType->sort_order ?? 0) }}"></label>
                <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $incidentType->is_active ?? true))> Aktif</label>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('incident-types.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
