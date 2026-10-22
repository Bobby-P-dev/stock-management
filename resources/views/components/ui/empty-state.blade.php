@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'py-10 px-6 text-center']) }}>
    @if (isset($icon))
        <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-500 border border-slate-200 mb-2.5">
            {{ $icon }}
        </div>
    @endif

    <p class="text-xs font-semibold text-slate-800">{{ $title }}</p>

    @if ($description)
        <p class="text-xs text-slate-500 mt-0.5 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-3 flex items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
