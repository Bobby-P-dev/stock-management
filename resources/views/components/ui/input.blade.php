@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'required' => false,
    'showRequiredIndicator' => null,
    'clearAction' => null,
    'showClear' => false,
])

@php
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $fieldName = $name ?? $wireModel;
    $inputId = $id ?? ($fieldName ? 'input-' . str_replace(['.', '_'], '-', $fieldName) : null);
    $errorMessage = $error ?? ($fieldName && isset($errors) ? $errors->first($fieldName) : null);
    $errorId = $inputId ? $inputId . '-error' : null;
    $hintId = $inputId ? $inputId . '-hint' : null;
    $describedBy = collect([
        $errorMessage ? $errorId : null,
        (! $errorMessage && $hint) ? $hintId : null,
    ])->filter()->implode(' ') ?: null;
    $hasIcon = isset($icon);
@endphp

<div class="w-full">
    @if ($label)
        <x-ui.label :for="$inputId" :required="$showRequiredIndicator ?? $required">
            {{ $label }}
        </x-ui.label>
    @endif

    <div class="relative">
        @if ($hasIcon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                {{ $icon }}
            </div>
        @endif

        <input
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($fieldName) name="{{ $fieldName }}" @endif
            type="{{ $type }}"
            @if ($required) required aria-required="true" @endif
            aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge([
                'class' => 'w-full h-[42px] ' . ($hasIcon ? 'pl-9 ' : 'pl-3.5 ') . ($showClear ? 'pr-8 ' : 'pr-3.5 ') . 'rounded-xl bg-white border ' . ($errorMessage ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 hover:border-slate-300 focus:border-blue-600 focus:ring-blue-600/20') . ' text-slate-900 text-xs placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed transition',
            ]) }}
        >

        @if ($showClear && $clearAction)
            <button
                type="button"
                wire:click="{{ $clearAction }}"
                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                title="Bersihkan input"
            >
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        @endif
    </div>

    @if ($errorMessage)
        <x-ui.field-error :id="$errorId" :messages="$errorMessage" />
    @elseif ($hint)
        <p @if ($hintId) id="{{ $hintId }}" @endif class="mt-1.5 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>
