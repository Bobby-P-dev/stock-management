@props([
    'align' => 'left',
])

@php
    $alignClasses = match ($align) {
        'right' => 'text-right',
        'center' => 'text-center',
        default => 'text-left',
    };
@endphp

<th scope="col" {{ $attributes->merge(['class' => "py-3 px-4 font-semibold {$alignClasses}"]) }}>
    {{ $slot }}
</th>
