@extends('layouts.app', ['title' => ($permission->exists ? 'Ubah' : 'Tambah').' Hak Akses | SI-DENI'])

@section('page-title', ($permission->exists ? 'Ubah' : 'Tambah').' Hak Akses')
@section('page-subtitle', 'Nama permission dinamis')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('permissions.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $permission->exists ? 'Ubah' : 'Tambah' }} hak akses">
            <form class="grid gap-5" method="POST" action="{{ $permission->exists ? route('permissions.update', $permission) : route('permissions.store') }}">
                @csrf @if ($permission->exists) @method('PUT') @endif
                <label class="label">Nama hak akses <span class="text-xs font-normal text-slate-500">(contoh: approve reports)</span>
                    <input class="field" name="name" value="{{ old('name', $permission->name) }}" required>
                </label>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('permissions.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
