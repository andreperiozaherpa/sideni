@extends('layouts.app', ['title' => 'Verifikasi Laporan | SI-DENI'])

@section('page-title', 'Verifikasi Laporan')
@section('page-subtitle', 'Laporan yang menunggu verifikasi')

@section('content')
    <div class="w-full space-y-6">
        <x-page-header title="Verifikasi Laporan" subtitle="Daftar laporan berstatus Menunggu yang perlu diperiksa kelengkapannya.">
            <x-slot:actions>
                <a class="link text-sm" href="{{ route('reports.index') }}">Lihat semua laporan</a>
            </x-slot:actions>
        </x-page-header>

        <x-panel>
            <x-datatable
                id="verification-table"
                :url="route('reports.verify.index')"
                :columns="[
                    ['data' => 'registration_number', 'title' => 'Nomor'],
                    ['data' => 'village_name', 'title' => 'Kampung', 'orderable' => false],
                    ['data' => 'category_name', 'title' => 'Kategori', 'orderable' => false],
                    ['data' => 'risk_label', 'title' => 'Risiko', 'orderable' => false],
                    ['data' => 'submitted_label', 'title' => 'Dilaporkan', 'orderable' => false, 'searchable' => false],
                    ['data' => 'sla', 'title' => 'SLA', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
