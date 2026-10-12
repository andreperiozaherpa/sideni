@extends('layouts.app', ['title' => 'Manajemen Hak Akses | SI-DENI'])

@section('page-title', 'Manajemen Hak Akses')
@section('page-subtitle', 'Kelola daftar permission dinamis')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Hak Akses">
            <x-slot:actions><x-button :href="route('permissions.create')" icon="plus">Tambah hak akses</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="permissions-table"
                :url="route('permissions.index')"
                :columns="[
                    ['data' => 'name', 'title' => 'Hak akses'],
                    ['data' => 'roles_count', 'title' => 'Dipakai role', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
