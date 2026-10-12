@props(['label', 'icon' => null, 'active' => false])

<div data-nav-group>
    <button type="button" data-submenu-toggle @class([
        'nav-item w-full justify-between',
        'nav-item-active' => $active,
        'nav-item-idle' => ! $active,
    ])>
        <span class="flex items-center gap-3">
            @if ($icon)
                <x-icon :name="$icon" class="h-5 w-5 shrink-0" />
            @endif
            <span class="truncate">{{ $label }}</span>
        </span>
        <x-icon name="chevron-down" class="h-4 w-4 shrink-0 transition-transform duration-200 {{ $active ? 'rotate-180' : '' }}" data-chevron />
    </button>
    <div data-submenu class="mt-1 ml-4 grid gap-1 border-l border-white/50 pl-3 {{ $active ? '' : 'hidden' }}">
        {{ $slot }}
    </div>
</div>
