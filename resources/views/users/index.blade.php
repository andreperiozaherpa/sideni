@extends('layouts.app', ['title' => 'Manajemen Pengguna | SI-DENI'])

@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Kelola akun dan penugasan role')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Pengguna">
            <x-slot:actions><x-button :href="route('users.create')" icon="plus">Tambah pengguna</x-button></x-slot:actions>
        </x-page-header>
        <x-panel>
            <x-datatable
                id="users-table"
                :url="route('users.index')"
                :columns="[
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'username', 'title' => 'Username'],
                    ['data' => 'email', 'title' => 'Email'],
                    ['data' => 'village_name', 'title' => 'Kampung', 'orderable' => false],
                    ['data' => 'roles_label', 'title' => 'Role', 'orderable' => false, 'searchable' => false],
                    ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
                ]"
            />
        </x-panel>
    </div>
@endsection
