@extends('layouts.app', ['title' => ($village->exists ? 'Ubah' : 'Tambah').' Kampung | SI-DENI'])

@section('page-title', ($village->exists ? 'Ubah' : 'Tambah').' Kampung')
@section('page-subtitle', 'Data referensi kampung')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('villages.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $village->exists ? 'Ubah' : 'Tambah' }} kampung">
            <form class="grid gap-5" method="POST" action="{{ $village->exists ? route('villages.update', $village) : route('villages.store') }}">
                @csrf @if ($village->exists) @method('PUT') @endif
                <label class="label">Kecamatan
                    <select class="select2 field" name="district_id" required>
                        <option value="">Pilih kecamatan</option>
                        @foreach ($districts as $district)<option value="{{ $district->id }}" @selected(old('district_id', $village->district_id) == $district->id)>{{ $district->name }}</option>@endforeach
                    </select>
                </label>
                <label class="label">Kode<input class="field" name="code" value="{{ old('code', $village->code) }}" required></label>
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $village->name) }}" required></label>
                <label class="label">Urutan<input class="field" type="number" min="0" name="sort_order" value="{{ old('sort_order', $village->sort_order ?? 0) }}" required></label>
                <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $village->is_active ?? true))> Aktif</label>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('villages.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
