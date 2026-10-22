@props([
    'label' => null,
    'description' => null,
    'id' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
    'card' => false,
])

@php
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $fieldName = $name ?? $wireModel;
    $inputId = $id ?? ($fieldName ? 'checkbox-' . str_replace(['.', '_'], '-', $fieldName) : 'checkbox-' . uniqid());
    $errorMessage = $error ?? ($fieldName && isset($errors) ? $errors->first($fieldName) : null);
    $errorId = $inputId . '-error';
    $hintId = $inputId . '-hint';
    $descriptionId = $inputId . '-description';

    $describedBy = collect([
        $description ? $descriptionId : null,
        $errorMessage ? $errorId : null,
        (! $errorMessage && $hint) ? $hintId : null,
    ])->filter()->implode(' ') ?: null;
@endphp

<div class="w-full">
    <label
        for="{{ $inputId }}"
        class="{{ $card ? 'flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 hover:border-slate-300/80 transition cursor-pointer select-none' : 'inline-flex items-start gap-2.5 cursor-pointer select-none' }}"
    >
        <div class="flex items-center h-5 shrink-0">
            <input
                id="{{ $inputId }}"
                @if ($name) name="{{ $name }}" @endif
                type="checkbox"
                aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                {{ $attributes->merge([
                    'class' => 'w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:ring-offset-0 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition cursor-pointer',
                ]) }}
            >
        </div>

        @if ($label || $description || $slot->isNotEmpty())
            <div class="min-w-0 flex-1">
                @if ($label || $slot->isNotEmpty())
                    <span class="block text-xs font-semibold text-slate-800 leading-5">
                        {{ $label ?? $slot }}
                    </span>
                @endif

                @if ($description)
                    <span id="{{ $descriptionId }}" class="block text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        {{ $description }}
                    </span>
                @endif
            </div>
        @endif
    </label>

    @if ($errorMessage)
        <x-ui.field-error :id="$errorId" :messages="$errorMessage" />
    @elseif ($hint)
        <p id="{{ $hintId }}" class="mt-1.5 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>
