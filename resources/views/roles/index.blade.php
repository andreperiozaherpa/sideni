@extends('layouts.app', ['title' => 'Manajemen Role | SI-DENI'])

@section('page-title', 'Manajemen Role')
@section('page-subtitle', 'Atur role dan hak akses')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Role">
            <x-slot:actions><x-button :href="route('roles.create')" icon="plus">Tambah role</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="roles-table"
                :url="route('roles.index')"
                :columns="[
                    ['data' => 'name', 'title' => 'Role'],
                    ['data' => 'permissions_count', 'title' => 'Jumlah hak akses', 'orderable' => false, 'searchable' => false],
                    ['data' => 'users_count', 'title' => 'Jumlah pengguna', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
