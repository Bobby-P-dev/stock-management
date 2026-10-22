@props([
    'label',
    'value',
    'description' => null,
    'tone' => 'blue',
])

@php
    $toneClasses = match ($tone) {
        'amber' => [
            'iconBg' => 'bg-amber-50 text-amber-600 border-amber-100',
            'valueText' => 'text-slate-900',
        ],
        'emerald' => [
            'iconBg' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
            'valueText' => 'text-slate-900',
        ],
        'rose' => [
            'iconBg' => 'bg-rose-50 text-rose-600 border-rose-100',
            'valueText' => 'text-slate-900',
        ],
        default => [
            'iconBg' => 'bg-blue-50 text-blue-600 border-blue-100',
            'valueText' => 'text-slate-900',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between h-full min-w-0']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1 min-w-0">
            <p class="text-xs font-medium text-slate-500 truncate">{{ $label }}</p>
            <p class="text-2xl sm:text-[28px] font-bold tracking-tight leading-none tabular-nums {{ $toneClasses['valueText'] }}">
                {{ $value }}
            </p>
        </div>

        @if (isset($icon))
            <div class="w-9 h-9 rounded-lg border flex items-center justify-center shrink-0 {{ $toneClasses['iconBg'] }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    @if ($description)
        <div class="mt-3 pt-2.5 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-1.5">
            {{ $description }}
        </div>
    @endif
</div>
