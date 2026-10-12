@extends('layouts.app', ['title' => 'Profil | SI-DENI'])

@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Perbarui informasi akun dan kata sandi')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <x-panel title="Profil Saya" subtitle="Perbarui informasi akun Anda.">
            <form class="grid gap-5" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="glass-soft flex items-center gap-4 p-4">
                    @if ($user->avatarUrl())
                        <img class="h-16 w-16 rounded-2xl border border-white/60 object-cover" src="{{ $user->avatarUrl() }}" alt="Foto profil">
                    @else
                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 text-xl font-bold text-white">{{ str($user->name)->substr(0, 1)->upper() }}</span>
                    @endif
                    <label class="label flex-1">Foto profil <span class="text-xs font-normal text-slate-500">(jpg/png/webp, maks. 2 MB)</span>
                        <input class="field" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                    </label>
                </div>
                <label class="label">Nama<input class="field" name="name" value="{{ old('name', $user->name) }}" required></label>
                <label class="label">Username<input class="field" name="username" value="{{ old('username', $user->username) }}" required></label>
                <label class="label">Email<input class="field" type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
                <label class="label">Kata sandi baru <span class="text-xs font-normal text-slate-500">(kosongkan jika tidak diubah)</span><input class="field" type="password" name="password"></label>
                <label class="label">Konfirmasi kata sandi baru<input class="field" type="password" name="password_confirmation"></label>
                <div class="flex justify-end"><x-button type="submit">Simpan perubahan</x-button></div>
            </form>
        </x-panel>
    </div>
@endsection
