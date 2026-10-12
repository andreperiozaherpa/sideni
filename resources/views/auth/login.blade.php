@extends('layouts.guest', ['title' => 'Masuk | SI-DENI Way Kanan'])

@section('content')
    <section class="glass grid w-full max-w-4xl overflow-hidden p-0 lg:grid-cols-2">
        <div class="hidden flex-col justify-between bg-gradient-to-br from-sky-600 to-indigo-700 p-10 text-white lg:flex">
            <div>
                <img class="h-16 w-16 object-contain drop-shadow-lg" src="{{ \App\Support\Branding::logoUrl() }}" alt="Lambang Kabupaten Way Kanan">
                <p class="mt-6 text-xs font-semibold tracking-[0.25em] text-sky-100">BADAN KESBANGPOL</p>
                <h1 class="mt-6 text-4xl font-bold leading-tight">SI-DENI<br>Way Kanan</h1>
                <p class="mt-6 leading-7 text-sky-100">Sistem Deteksi Dini Konflik Sosial Terintegrasi.</p>
            </div>
            <p class="text-xs text-sky-200">Cepat • Faktual • Terverifikasi • Berbasis Bukti</p>
        </div>
        <div class="p-8 sm:p-10">
            <img class="mb-5 h-14 w-14 object-contain lg:hidden" src="{{ \App\Support\Branding::logoUrl() }}" alt="Lambang Kabupaten Way Kanan">
            <p class="text-sm font-semibold text-sky-700">AKSES PENGGUNA</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">Masuk ke SI-DENI</h2>
            <form class="mt-8 grid gap-5" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label class="label">Email atau Username
                    <input class="field" type="text" name="login" value="{{ old('login') }}" required autofocus>
                    @error('login') <span class="text-sm font-normal text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="label">Kata sandi
                    <input class="field" type="password" name="password" required>
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input class="size-4 rounded border-slate-300 text-sky-700" type="checkbox" name="remember" value="1"> Ingat saya</label>
                <x-button type="submit" class="w-full">Masuk</x-button>
            </form>
        </div>
    </section>
@endsection
