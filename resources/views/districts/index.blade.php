@extends('layouts.app', ['title' => 'Kecamatan | SI-DENI'])

@section('page-title', 'Kecamatan')
@section('page-subtitle', 'Data kecamatan Kabupaten Way Kanan')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Kecamatan">
            <x-slot:actions><x-button :href="route('districts.create')" icon="plus">Tambah kecamatan</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="districts-table"
                :url="route('districts.index')"
                :columns="[
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'villages_count', 'title' => 'Kampung', 'orderable' => false, 'searchable' => false],
                    ['data' => 'status_label', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
