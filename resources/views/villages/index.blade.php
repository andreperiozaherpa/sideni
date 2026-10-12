@extends('layouts.app', ['title' => 'Kampung | SI-DENI'])

@section('page-title', 'Kampung')
@section('page-subtitle', 'Data kampung dan kelurahan')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Kampung">
            <x-slot:actions><x-button :href="route('villages.create')" icon="plus">Tambah kampung</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="villages-table"
                :url="route('villages.index')"
                :columns="[
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Kampung'],
                    ['data' => 'district_name', 'title' => 'Kecamatan', 'orderable' => false, 'searchable' => false],
                    ['data' => 'status_label', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
