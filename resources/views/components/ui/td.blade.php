@props([
    'align' => 'left',
    'numeric' => false,
])

@php
    $alignClasses = match ($align) {
        'right' => 'text-right',
        'center' => 'text-center',
        default => 'text-left',
    };

    $numericClasses = $numeric ? 'tabular-nums whitespace-nowrap' : '';
@endphp

<td {{ $attributes->merge(['class' => trim("py-3.5 px-4 align-middle {$alignClasses} {$numericClasses}")]) }}>
    {{ $slot }}
</td>
