<!DOCTYPE html>
<html lang="id" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'SI-DENI Way Kanan' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-full">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-sky-300/50 blur-3xl"></div>
            <div class="absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-indigo-300/50 blur-3xl"></div>
            <div class="absolute left-1/3 top-1/3 h-96 w-96 rounded-full bg-cyan-200/50 blur-3xl"></div>
        </div>

        <main class="flex min-h-screen items-center justify-center p-4">
            @yield('content')
        </main>

        @stack('scripts')
    </body>
</html>
