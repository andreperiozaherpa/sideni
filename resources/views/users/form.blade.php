@extends('layouts.app', ['title' => ($user->exists ? 'Ubah' : 'Tambah').' Pengguna | SI-DENI'])

@section('page-title', ($user->exists ? 'Ubah' : 'Tambah').' Pengguna')
@section('page-subtitle', 'Data akun dan penugasan role')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <a class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700" href="{{ route('users.index') }}"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        <x-panel title="{{ $user->exists ? 'Ubah' : 'Tambah' }} pengguna">
            <form class="grid gap-5" method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
                @csrf @if ($user->exists) @method('PUT') @endif
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $user->name) }}" required></label>
                <label class="label">Username<input class="field" name="username" value="{{ old('username', $user->username) }}" required></label>
                <label class="label">Email<input class="field" type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
                <label class="label">Kata sandi {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}<input class="field" type="password" name="password" {{ $user->exists ? '' : 'required' }}></label>
                <label class="label">Konfirmasi kata sandi<input class="field" type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }}></label>
                <fieldset class="grid gap-2">
                    <legend class="text-sm font-medium text-slate-700">Role</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($roles as $role)
                            <label class="glass-soft flex items-center gap-2 px-3 py-2 text-sm"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', $user->roles->modelKeys())))> {{ $role->name }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="flex justify-end gap-3">
                    <x-button variant="ghost" :href="route('users.index')">Batal</x-button>
                    <x-button type="submit">Simpan</x-button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection
