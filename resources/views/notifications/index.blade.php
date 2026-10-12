@extends('layouts.app', ['title' => 'Notifikasi | SI-DENI'])

@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Pembaruan status laporan Anda')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <x-page-header title="Notifikasi">
            <x-slot:actions>
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<x-button type="submit" variant="ghost">Tandai semua dibaca</x-button></form>
            </x-slot:actions>
        </x-page-header>

        <div class="space-y-3">
            @forelse ($notifications as $notification)
                <article class="glass flex items-start justify-between gap-4 p-4 {{ $notification->read_at ? '' : 'border-sky-200/70 bg-sky-50/60' }}">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $notification->data['message'] ?? 'Pembaruan laporan.' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<x-button type="submit" :variant="$notification->read_at ? 'ghost' : 'primary'">{{ $notification->read_at ? 'Buka' : 'Baca' }}</x-button></form>
                </article>
            @empty
                <x-panel><p class="text-sm text-slate-500">Belum ada notifikasi.</p></x-panel>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
@endsection
