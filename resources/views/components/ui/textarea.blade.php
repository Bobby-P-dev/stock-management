@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'rows' => 3,
    'error' => null,
    'hint' => null,
    'required' => false,
])

@php
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $fieldName = $name ?? $wireModel;
    $inputId = $id ?? ($fieldName ? 'textarea-' . str_replace(['.', '_'], '-', $fieldName) : null);
    $errorMessage = $error ?? ($fieldName && isset($errors) ? $errors->first($fieldName) : null);
    $errorId = $inputId ? $inputId . '-error' : null;
    $hintId = $inputId ? $inputId . '-hint' : null;
    $describedBy = collect([
        $errorMessage ? $errorId : null,
        (! $errorMessage && $hint) ? $hintId : null,
    ])->filter()->implode(' ') ?: null;
@endphp

<div class="w-full">
    @if ($label)
        <x-ui.label :for="$inputId" :required="$required">
            {{ $label }}
        </x-ui.label>
    @endif

    <textarea
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($fieldName) name="{{ $fieldName }}" @endif
        rows="{{ $rows }}"
        @if ($required) required aria-required="true" @endif
        aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full px-3.5 py-2.5 rounded-xl bg-white border ' . ($errorMessage ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 hover:border-slate-300 focus:border-blue-600 focus:ring-blue-600/20') . ' text-slate-900 text-xs placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed transition leading-relaxed',
        ]) }}
    ></textarea>

    @if ($errorMessage)
        <x-ui.field-error :id="$errorId" :messages="$errorMessage" />
    @elseif ($hint)
        <p @if ($hintId) id="{{ $hintId }}" @endif class="mt-1.5 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>
