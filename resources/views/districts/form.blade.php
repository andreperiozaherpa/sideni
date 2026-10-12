@extends('layouts.app', ['title' => ($district->exists ? 'Ubah' : 'Tambah').' Kecamatan | SI-DENI'])

@section('page-title', ($district->exists ? 'Ubah' : 'Tambah').' Kecamatan')
@section('page-subtitle', 'Data referensi kecamatan')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('districts.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $district->exists ? 'Ubah' : 'Tambah' }} kecamatan">
            <form class="grid gap-5" method="POST" action="{{ $district->exists ? route('districts.update', $district) : route('districts.store') }}">
                @csrf @if ($district->exists) @method('PUT') @endif
                <label class="label">Kode<input class="field" name="code" value="{{ old('code', $district->code) }}" required></label>
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $district->name) }}" required></label>
                <label class="label">Urutan<input class="field" type="number" min="0" name="sort_order" value="{{ old('sort_order', $district->sort_order ?? 0) }}" required></label>
                <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $district->is_active ?? true))> Aktif</label>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('districts.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
