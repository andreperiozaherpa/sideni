<!DOCTYPE html>
<html lang="id" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'SI-DENI Way Kanan' }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-full">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-sky-300/40 blur-3xl"></div>
            <div class="absolute -right-24 top-1/3 h-96 w-96 rounded-full bg-indigo-300/40 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-96 w-96 rounded-full bg-cyan-200/40 blur-3xl"></div>
        </div>

        <div class="flex min-h-screen">
            <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>

            <aside data-sidebar class="glass fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col rounded-none border-y-0 border-l-0 border-r border-white/50 p-5 transition-transform duration-300 lg:translate-x-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-1">
                    <img class="h-11 w-11 object-contain" src="{{ \App\Support\Branding::logoUrl() }}" alt="Lambang Kabupaten Way Kanan">
                    <span>
                        <span class="block text-base font-bold tracking-tight text-slate-800">SI-DENI</span>
                        <span class="block text-xs text-slate-500">Way Kanan</span>
                    </span>
                </a>

                <div class="mt-6" data-profile-menu>
                    <button type="button" data-profile-toggle class="glass-soft flex w-full items-center gap-3 p-3 text-left transition hover:bg-white/80">
                        @if (auth()->user()->avatarUrl())
                            <img class="h-10 w-10 rounded-full object-cover" src="{{ auth()->user()->avatarUrl() }}" alt="Foto profil">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-sm font-bold text-white">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ auth()->user()->getRoleNames()->join(', ') ?: 'Tanpa role' }}</span>
                        </span>
                        <x-icon name="chevron-down" class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" data-profile-chevron />
                    </button>

                    <div data-profile-dropdown class="mt-1 hidden overflow-hidden rounded-xl border border-white/50 bg-white/80 p-1 shadow-lg backdrop-blur-xl">
                        <a href="{{ route('profile.edit') }}" class="nav-item nav-item-idle w-full"><x-icon name="user" class="h-4 w-4" /> Profil</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nav-item w-full text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="h-4 w-4" /> Keluar</button></form>
                    </div>
                </div>

                <div class="mt-7 flex-1 overflow-y-auto pb-4">
                    <nav class="grid gap-1">
                        <x-nav-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>

                        <x-nav-group label="Laporan" icon="document" :active="request()->routeIs('reports.*')">
                            <x-nav-sublink :href="route('reports.index')" :active="request()->routeIs('reports.index', 'reports.show', 'reports.edit')">Daftar Laporan</x-nav-sublink>
                            @can('create reports')
                                <x-nav-sublink :href="route('reports.create')" :active="request()->routeIs('reports.create')">Buat Laporan</x-nav-sublink>
                            @endcan
                            @can('verify reports')
                                <x-nav-sublink :href="route('reports.verify.index')" :active="request()->routeIs('reports.verify.index', 'reports.verify.show')">
                                    Verifikasi
                                    @if (($pendingVerificationCount ?? 0) > 0)
                                        <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ $pendingVerificationCount }}</span>
                                    @endif
                                </x-nav-sublink>
                            @endcan
                        </x-nav-group>

                        <x-nav-link :href="route('notifications.index')" icon="bell" :active="request()->routeIs('notifications.*')">Notifikasi</x-nav-link>

                        @can('manage master data')
                            <x-nav-group label="Data Master" icon="folder" :active="request()->routeIs('report-categories.*', 'incident-types.*', 'districts.*', 'villages.*', 'settings.*')">
                                <x-nav-sublink :href="route('report-categories.index')" :active="request()->routeIs('report-categories.*')">Kategori Kerawanan</x-nav-sublink>
                                <x-nav-sublink :href="route('incident-types.index')" :active="request()->routeIs('incident-types.*')">Jenis Kejadian</x-nav-sublink>
                                <x-nav-sublink :href="route('districts.index')" :active="request()->routeIs('districts.*')">Kecamatan</x-nav-sublink>
                                <x-nav-sublink :href="route('villages.index')" :active="request()->routeIs('villages.*')">Kampung</x-nav-sublink>
                                <x-nav-sublink :href="route('settings.edit')" :active="request()->routeIs('settings.*')">Pengaturan</x-nav-sublink>
                            </x-nav-group>
                        @endcan

                        @if (auth()->user()->can('manage users') || auth()->user()->can('manage roles') || auth()->user()->can('manage permissions'))
                            <x-nav-group label="Akses & Peran" icon="shield" :active="request()->routeIs('users.*', 'roles.*', 'permissions.*')">
                                @can('manage users')<x-nav-sublink :href="route('users.index')" :active="request()->routeIs('users.*')">Pengguna</x-nav-sublink>@endcan
                                @can('manage roles')<x-nav-sublink :href="route('roles.index')" :active="request()->routeIs('roles.*')">Role</x-nav-sublink>@endcan
                                @can('manage permissions')<x-nav-sublink :href="route('permissions.index')" :active="request()->routeIs('permissions.*')">Hak Akses</x-nav-sublink>@endcan
                            </x-nav-group>
                        @endif
                    </nav>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
                <header class="glass sticky top-0 z-20 flex items-center justify-between gap-4 rounded-none border-x-0 border-t-0 border-b border-white/50 px-4 py-3.5 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button data-sidebar-toggle class="rounded-lg p-2 text-slate-600 transition hover:bg-white/70 lg:hidden" aria-label="Buka menu"><x-icon name="menu" class="h-5 w-5" /></button>
                        <div>
                            <h1 class="text-base font-semibold text-slate-800 sm:text-lg">@yield('page-title', 'Dashboard')</h1>
                            <p class="hidden text-xs text-slate-500 sm:block">@yield('page-subtitle', 'Sistem Deteksi Dini Konflik Sosial')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('notifications.index') }}" class="relative rounded-xl border border-white/60 bg-white/70 p-2.5 text-slate-600 transition hover:bg-white" title="Notifikasi">
                            <x-icon name="bell" class="h-5 w-5" />
                            @if (($unreadCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-r from-rose-500 to-red-600 px-1 text-[11px] font-bold text-white shadow">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl border border-white/60 bg-white/70 p-1.5 pr-3 transition hover:bg-white">
                            @if (auth()->user()->avatarUrl())
                                <img class="h-8 w-8 rounded-lg object-cover" src="{{ auth()->user()->avatarUrl() }}" alt="Foto profil">
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-indigo-500 text-xs font-bold text-white">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                            @endif
                            <span class="hidden text-sm font-semibold text-slate-700 sm:block">Profil</span>
                        </a>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </main>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
        <script>
            window.__flash = {
                success: @json(session('status')),
                errors: @json($errors->all()),
            };
        </script>
        @stack('scripts')
    </body>
</html>
