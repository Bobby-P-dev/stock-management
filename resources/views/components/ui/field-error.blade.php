@props([
    'messages' => null,
    'id' => null,
])

@php
    $errorText = is_array($messages) ? ($messages[0] ?? null) : $messages;
@endphp

@if ($errorText)
    <p
        @if ($id) id="{{ $id }}" @endif
        role="alert"
        {{ $attributes->merge(['class' => 'mt-1.5 text-[11px] font-medium text-rose-600 leading-normal']) }}
    >
        {{ $errorText }}
    </p>
@endif
