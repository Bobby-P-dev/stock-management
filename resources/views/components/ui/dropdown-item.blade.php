@props([
    'variant' => 'default',
    'href' => null,
])

@php
    $variantClasses = match ($variant) {
        'danger' => 'text-rose-600 hover:bg-rose-50/80 focus-visible:ring-rose-500',
        default => 'text-slate-700 hover:bg-slate-50 focus-visible:ring-blue-600',
    };

    $classes = "w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->merge(['class' => $classes]) }}>
        @if (isset($icon))
            <span class="shrink-0 flex items-center">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="button" role="menuitem" {{ $attributes->merge(['class' => $classes]) }}>
        @if (isset($icon))
            <span class="shrink-0 flex items-center">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
