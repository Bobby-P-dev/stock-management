@props([
    'loadingTarget' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs relative']) }}>
    @if ($loadingTarget)
        <div
            wire:loading.delay
            wire:target="{{ $loadingTarget }}"
            class="absolute inset-x-0 top-0 h-0.5 bg-blue-600 animate-pulse z-20"
        ></div>
    @endif

    @if (isset($header))
        {{ $header }}
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            @if (isset($head))
                <thead>
                    <tr class="text-slate-500 bg-slate-50/80 border-b border-slate-200">
                        {{ $head }}
                    </tr>
                </thead>
            @endif

            <tbody
                class="divide-y divide-slate-100"
                @if ($loadingTarget) wire:loading.class="opacity-60" wire:target="{{ $loadingTarget }}" @endif
            >
                {{ $slot }}
            </tbody>

            @if (isset($tfoot))
                <tfoot>
                    {{ $tfoot }}
                </tfoot>
            @endif
        </table>
    </div>

    @if (isset($footer))
        <div class="px-4 py-3.5 border-t border-slate-200 bg-slate-50/40">
            {{ $footer }}
        </div>
    @endif
</div>
