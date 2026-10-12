@props(['href', 'icon' => null, 'active' => false])

<a href="{{ $href }}" @class(['nav-item', 'nav-item-active' => $active, 'nav-item-idle' => ! $active])>
    @if ($icon)
        <x-icon :name="$icon" class="h-5 w-5 shrink-0" />
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
