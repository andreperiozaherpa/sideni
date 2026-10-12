@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'glass p-6']) }}>
    @if ($title || isset($actions))
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h2 class="text-base font-semibold text-slate-800">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</section>
