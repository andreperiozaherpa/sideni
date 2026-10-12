@extends('layouts.app', ['title' => 'Kategori Kerawanan | SI-DENI'])

@section('page-title', ($category->exists ? 'Ubah' : 'Tambah').' Kategori')
@section('page-subtitle', 'Data referensi kategori kerawanan')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('report-categories.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $category->exists ? 'Ubah' : 'Tambah' }} kategori">
            <form class="grid gap-5" method="POST" action="{{ $category->exists ? route('report-categories.update', $category) : route('report-categories.store') }}">
                @csrf @if ($category->exists) @method('PUT') @endif
                <label class="label">Kode<input class="field" name="code" value="{{ old('code', $category->code) }}" required></label>
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $category->name) }}" required></label>
                <label class="label">Urutan<input class="field" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" required></label>
                <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Aktif</label>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('report-categories.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
