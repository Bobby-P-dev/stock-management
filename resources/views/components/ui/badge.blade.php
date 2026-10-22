@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
    $variantClasses = match ($variant) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        'info' => 'bg-blue-50 text-blue-700 border-blue-200',
        'mono' => 'font-mono font-medium text-slate-600 bg-slate-100/80 border-slate-200/70',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };

    $sizeClasses = match ($size) {
        'xs' => 'px-1.5 py-0.5 rounded text-[10px] font-semibold',
        'code' => 'px-2 py-0.5 rounded-md text-xs',
        default => 'px-2.5 py-0.5 rounded-md text-[11px] font-semibold',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center border {$variantClasses} {$sizeClasses}"]) }}>
    {{ $slot }}
</span>
