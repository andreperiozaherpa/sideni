@props(['variant' => 'primary', 'href' => null, 'icon' => null])

@php
    $classes = 'btn btn-'.$variant;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </button>
@endif
