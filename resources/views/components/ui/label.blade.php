@props([
    'for' => null,
    'required' => false,
])

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-xs font-semibold text-slate-700 mb-1.5 leading-snug']) }}
>
    {{ $slot }}
    @if ($required)
        <span class="text-rose-500 ml-0.5 select-none" aria-hidden="true">*</span>
    @endif
</label>
