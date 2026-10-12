@extends('layouts.app', ['title' => 'Pengaturan | SI-DENI'])

@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Identitas instansi dan pejabat penandatangan')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <x-panel title="Pengaturan" subtitle="Identitas instansi dan pejabat penandatangan dokumen.">
            <form class="grid gap-6" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <fieldset class="grid gap-4">
                    <legend class="text-sm font-bold text-slate-700">Identitas instansi</legend>
                    <label class="label">Nama badan/instansi<input class="field" name="institution_name" value="{{ old('institution_name', $settings['institution_name']) }}" required></label>
                    <label class="label">Alamat<input class="field" name="institution_address" value="{{ old('institution_address', $settings['institution_address']) }}"></label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="label">Telepon<input class="field" name="institution_phone" value="{{ old('institution_phone', $settings['institution_phone']) }}"></label>
                        <label class="label">Email<input class="field" type="email" name="institution_email" value="{{ old('institution_email', $settings['institution_email']) }}"></label>
                    </div>
                    <div class="glass-soft flex items-center gap-4 p-4">
                        @if ($settings['institution_logo'])
                            <img class="h-16 w-16 rounded-xl border border-white/60 object-contain" src="{{ Storage::disk('public')->url($settings['institution_logo']) }}" alt="Logo instansi">
                        @endif
                        <label class="label flex-1">Logo instansi <span class="text-xs font-normal text-slate-500">(jpg/png/webp, maks. 2 MB)</span>
                            <input class="field" type="file" name="institution_logo" accept="image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </fieldset>
                <fieldset class="grid gap-4">
                    <legend class="text-sm font-bold text-slate-700">Pejabat penandatangan</legend>
                    <label class="label">Nama Kepala Badan<input class="field" name="head_name" value="{{ old('head_name', $settings['head_name']) }}"></label>
                    <label class="label">Pangkat / Golongan<input class="field" name="head_rank" value="{{ old('head_rank', $settings['head_rank']) }}" placeholder="Contoh: Pembina Utama Muda"></label>
                    <label class="label">NIP Kepala Badan<input class="field" name="head_nip" value="{{ old('head_nip', $settings['head_nip']) }}"></label>
                </fieldset>
                <div class="flex justify-end"><x-button type="submit">Simpan pengaturan</x-button></div>
            </form>
        </x-panel>
    </div>
@endsection
