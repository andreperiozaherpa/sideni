@extends('layouts.app', ['title' => 'Laporan | SI-DENI'])

@section('page-title', 'Laporan')
@section('page-subtitle', 'Daftar dan penugasan laporan deteksi dini')

@php
    $statusTabs = [
        '' => ['label' => 'Semua', 'count' => array_sum($statusCounts)],
        'pending' => ['label' => 'Menunggu', 'count' => $statusCounts['pending'] ?? 0],
        'needs_clarification' => ['label' => 'Perlu Klarifikasi', 'count' => $statusCounts['needs_clarification'] ?? 0],
        'verified' => ['label' => 'Diverifikasi', 'count' => $statusCounts['verified'] ?? 0],
        'in_progress' => ['label' => 'Diproses', 'count' => $statusCounts['in_progress'] ?? 0],
        'completed' => ['label' => 'Selesai', 'count' => $statusCounts['completed'] ?? 0],
        'rejected' => ['label' => 'Ditolak', 'count' => $statusCounts['rejected'] ?? 0],
    ];
@endphp

@section('content')
    <div class="w-full space-y-6">
        <x-page-header title="Laporan dan Penugasan" subtitle="Seluruh laporan yang menjadi kewenangan Anda.">
            <x-slot:actions>
                @can('verify reports')
                    <x-button variant="ghost" :href="route('reports.verify.index')" icon="shield">Verifikasi</x-button>
                @endcan
                @can('create reports')
                    <x-button :href="route('reports.create')" icon="plus">Buat laporan</x-button>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="glass flex flex-wrap gap-2 p-2">
            @foreach ($statusTabs as $key => $tab)
                <a href="{{ route('reports.index', array_filter(['status' => $key])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition',
                       'bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-lg shadow-sky-500/25' => $currentStatus === $key,
                       'text-slate-600 hover:bg-white/70' => $currentStatus !== $key,
                   ])>
                    {{ $tab['label'] }}
                    <span @class(['rounded-full px-2 py-0.5 text-xs font-semibold', 'bg-white/25' => $currentStatus === $key, 'bg-white/70 text-slate-500' => $currentStatus !== $key])>{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        <x-panel>
            <x-datatable
                id="reports-table"
                :url="route('reports.index', array_filter(['status' => $currentStatus]))"
                :columns="[
                    ['data' => 'registration_number', 'title' => 'Nomor'],
                    ['data' => 'village_name', 'title' => 'Kampung', 'orderable' => false],
                    ['data' => 'category_name', 'title' => 'Kategori', 'orderable' => false],
                    ['data' => 'risk_label', 'title' => 'Risiko', 'orderable' => false],
                    ['data' => 'status_label', 'title' => 'Status', 'orderable' => false],
                    ['data' => 'created_label', 'title' => 'Dibuat', 'orderable' => false, 'searchable' => false],
                    ['data' => 'sla', 'title' => 'SLA', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
