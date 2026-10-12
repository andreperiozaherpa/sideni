@extends('layouts.app', ['title' => ($role->exists ? 'Ubah' : 'Tambah').' Role | SI-DENI'])

@section('page-title', ($role->exists ? 'Ubah' : 'Tambah').' Role')
@section('page-subtitle', 'Nama role dan hak aksesnya')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('roles.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $role->exists ? 'Ubah' : 'Tambah' }} role">
            <form class="grid gap-5" method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}">
                @csrf @if ($role->exists) @method('PUT') @endif
                <label class="label">Nama role<input class="field" name="name" value="{{ old('name', $role->name) }}" required></label>
                <fieldset class="grid gap-2">
                    <legend class="text-sm font-medium text-slate-700">Hak akses</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', $role->permissions->modelKeys())))> {{ $permission->name }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('roles.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
