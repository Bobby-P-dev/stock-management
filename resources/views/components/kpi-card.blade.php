@props([
    'label',
    'value',
    'unit' => null,
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
        'slate' => [
            'iconBg' => 'bg-slate-50 text-slate-600 border-slate-200',
            'valueText' => 'text-slate-900',
        ],
        default => [
            'iconBg' => 'bg-blue-50 text-blue-600 border-blue-100',
            'valueText' => 'text-slate-900',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between h-full min-w-0']) }}>
    <div>
        <div class="flex items-center justify-between gap-2 mb-2">
            <p class="text-xs font-medium text-slate-500 truncate" title="{{ $label }}">{{ $label }}</p>
            @if (isset($icon))
                <div class="w-8 h-8 rounded-lg border flex items-center justify-center shrink-0 {{ $toneClasses['iconBg'] }}">
                    {{ $icon }}
                </div>
            @endif
        </div>

        @if(is_string($value) && str_starts_with($value, 'Rp '))
            <p class="text-xl sm:text-2xl font-bold tracking-tight leading-tight tabular-nums truncate {{ $toneClasses['valueText'] }}" title="{{ $value }}">
                <span class="text-xs sm:text-sm font-semibold text-slate-400 mr-0.5">Rp</span>{{ substr($value, 3) }}
                @if ($unit)
                    <span class="text-xs font-normal text-slate-500 ml-1">{{ $unit }}</span>
                @endif
            </p>
        @else
            <p class="text-2xl sm:text-[28px] font-bold tracking-tight leading-none tabular-nums truncate {{ $toneClasses['valueText'] }}" title="{{ $value }}">
                {{ $value }}
                @if ($unit)
                    <span class="text-xs font-normal text-slate-500 ml-1">{{ $unit }}</span>
                @endif
            </p>
        @endif
    </div>

    @if ($description)
        <div class="mt-3 pt-2.5 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-1.5">
            {{ $description }}
        </div>
    @endif
</div>
