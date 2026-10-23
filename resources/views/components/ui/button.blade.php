@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'loadingTarget' => null,
    'loadingText' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 disabled:opacity-60 disabled:cursor-not-allowed shrink-0';

    $variantClasses = match ($variant) {
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-2xs focus-visible:ring-blue-600',
        'secondary' => 'bg-blue-50/80 hover:bg-blue-100/80 text-blue-600 border border-blue-200/80 focus-visible:ring-blue-600',
        'outline' => 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 shadow-2xs focus-visible:ring-blue-600',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-600 hover:text-slate-900 focus-visible:ring-slate-400',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-2xs focus-visible:ring-rose-600',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs focus-visible:ring-emerald-600',
        default => 'bg-blue-600 hover:bg-blue-700 text-white shadow-2xs focus-visible:ring-blue-600',
    };

    $sizeClasses = match ($size) {
        'xs' => 'gap-1 px-2.5 py-1.5 rounded-lg text-xs',
        'sm' => 'gap-1.5 px-3 py-2 rounded-xl text-xs',
        'icon' => 'w-8 h-8 rounded-lg text-xs',
        default => 'gap-2 px-4 py-2.5 rounded-xl text-xs',
    };

    $classes = "{$baseClasses} {$variantClasses} {$sizeClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if (isset($icon))
            <span class="shrink-0 flex items-center">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($loadingTarget)
            wire:loading.attr="disabled"
            wire:target="{{ $loadingTarget }}"
        @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($loadingTarget)
            <svg
                wire:loading
                wire:target="{{ $loadingTarget }}"
                class="animate-spin h-3.5 w-3.5 shrink-0"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        @endif

        @if (isset($icon))
            <span
                @if ($loadingTarget) wire:loading.remove wire:target="{{ $loadingTarget }}" @endif
                class="shrink-0 flex items-center"
            >
                {{ $icon }}
            </span>
        @endif

        @if ($loadingTarget && $loadingText)
            <span wire:loading.remove wire:target="{{ $loadingTarget }}">
                {{ $slot }}
            </span>
            <span wire:loading wire:target="{{ $loadingTarget }}">
                {{ $loadingText }}
            </span>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
