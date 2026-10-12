@extends('layouts.app', ['title' => 'Jenis Kejadian | SI-DENI'])

@section('page-title', 'Jenis Kejadian')
@section('page-subtitle', 'Jenis kejadian per kategori kerawanan')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Jenis Kejadian">
            <x-slot:actions><x-button :href="route('incident-types.create')" icon="plus">Tambah jenis</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="incident-types-table"
                :url="route('incident-types.index')"
                :columns="[
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Jenis'],
                    ['data' => 'category_name', 'title' => 'Kategori', 'orderable' => false, 'searchable' => false],
                    ['data' => 'status_label', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
