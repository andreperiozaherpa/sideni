@extends('layouts.app', ['title' => 'Kategori Kerawanan | SI-DENI'])

@section('page-title', 'Kategori Kerawanan')
@section('page-subtitle', 'Klasifikasi kerawanan untuk form laporan')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Kategori Kerawanan">
            <x-slot:actions><x-button :href="route('report-categories.create')" icon="plus">Tambah kategori</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="report-categories-table"
                :url="route('report-categories.index')"
                :columns="[
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'status_label', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
                    ['data' => 'sort_order', 'title' => 'Urutan'],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
