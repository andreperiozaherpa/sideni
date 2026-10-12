@props(['href', 'active' => false])

<a href="{{ $href }}" @class([
    'nav-item',
    'bg-white/80 text-sky-700 shadow-sm' => $active,
    'text-slate-500 hover:bg-white/60 hover:text-slate-800' => ! $active,
])>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-60"></span>
    <span class="truncate">{{ $slot }}</span>
</a>
