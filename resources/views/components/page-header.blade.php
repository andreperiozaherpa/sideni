@props(['title', 'subtitle' => null])

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
