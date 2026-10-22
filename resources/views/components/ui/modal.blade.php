@props([
    'open' => true,
    'id' => 'ui-modal',
    'title' => '',
    'description' => null,
    'maxWidth' => 'lg',
    'closeAction' => 'closeModal',
    'loadingTarget' => 'save',
    'formAction' => null,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-md',
        'md' => 'sm:max-w-lg',
        'lg' => 'sm:max-w-[600px]',
        'xl' => 'sm:max-w-2xl',
        '2xl' => 'sm:max-w-3xl',
        default => 'sm:max-w-[600px]',
    };

    $titleId = $id . '-title';
    $descriptionId = $id . '-description';
@endphp

@if ($open)
    <div
        id="{{ $id }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        @if ($description) aria-describedby="{{ $descriptionId }}" @endif
        x-data="{
            triggerEl: null,
            init() {
                this.triggerEl = document.activeElement;
                document.body.classList.add('overflow-hidden');
                this.$nextTick(() => {
                    this.focusFirst();
                });
            },
            destroy() {
                document.body.classList.remove('overflow-hidden');
                if (this.triggerEl && document.body.contains(this.triggerEl) && typeof this.triggerEl.focus === 'function') {
                    setTimeout(() => {
                        if (this.triggerEl && document.body.contains(this.triggerEl)) {
                            this.triggerEl.focus();
                        }
                    }, 10);
                }
            },
            focusableElements() {
                const selector = 'a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])';
                return Array.from(this.$refs.dialogPanel?.querySelectorAll(selector) || []).filter(el => el.offsetParent !== null);
            },
            focusFirst() {
                const firstInput = this.$refs.dialogBody?.querySelector('input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
                if (firstInput) {
                    firstInput.focus();
                    return;
                }
                const focusables = this.focusableElements();
                if (focusables.length > 0) {
                    focusables[0].focus();
                }
            },
            handleTab(e) {
                const focusables = this.focusableElements();
                if (focusables.length === 0) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            },
            handleEscape(e) {
                if (e._dropdownClosed) return;
                @if ($closeOnEscape)
                    if (this.$refs.closeBtn && !this.$refs.closeBtn.disabled) {
                        this.$refs.closeBtn.click();
                    }
                @endif
            },
            handleBackdropClick() {
                @if ($closeOnBackdrop)
                    if (this.$refs.closeBtn && !this.$refs.closeBtn.disabled) {
                        this.$refs.closeBtn.click();
                    }
                @endif
            }
        }"
        @keydown.escape.window="handleEscape($event)"
        @keydown.tab="handleTab($event)"
        {{ $attributes->merge(['class' => 'fixed inset-0 z-50 overflow-y-auto overflow-x-hidden flex min-h-full items-center justify-center p-4 sm:p-6']) }}
    >
        {{-- Backdrop Overlay --}}
        <div
            class="fixed inset-0 bg-slate-900/45 backdrop-blur-[2px] transition-opacity"
            aria-hidden="true"
            @click="handleBackdropClick()"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-ref="dialogPanel"
            class="relative z-10 w-full {{ $maxWidthClass }} bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-900/10 flex flex-col max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-3rem)] min-w-0"
        >
            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-4 px-5 pt-5 pb-4 sm:px-6 sm:pt-6 sm:pb-4 border-b border-slate-100 shrink-0">
                <div class="min-w-0 flex-1">
                    <h3 id="{{ $titleId }}" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                        {{ $title }}
                    </h3>
                    @if ($description)
                        <p id="{{ $descriptionId }}" class="mt-1 text-xs sm:text-sm text-slate-500 leading-relaxed">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                <button
                    x-ref="closeBtn"
                    type="button"
                    wire:click="{{ $closeAction }}"
                    @if ($loadingTarget)
                        wire:loading.attr="disabled"
                        wire:target="{{ $loadingTarget }}"
                    @endif
                    aria-label="Tutup modal"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shrink-0 -mr-1 -mt-0.5"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            @if ($formAction)
                <form wire:submit="{{ $formAction }}" class="flex flex-col flex-1 min-h-0 min-w-0">
                    {{-- Modal Body --}}
                    <div x-ref="dialogBody" class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 min-w-0">
                        {{ $slot }}
                    </div>

                    {{-- Modal Footer --}}
                    @isset($footer)
                        <div class="px-5 py-4 sm:px-6 sm:py-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-end gap-3 shrink-0">
                            {{ $footer }}
                        </div>
                    @endisset
                </form>
            @else
                {{-- Modal Body --}}
                <div x-ref="dialogBody" class="p-5 sm:p-6 overflow-y-auto flex-1 min-h-0 min-w-0">
                    {{ $slot }}
                </div>

                {{-- Modal Footer --}}
                @isset($footer)
                    <div class="px-5 py-4 sm:px-6 sm:py-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-end gap-3 shrink-0">
                        {{ $footer }}
                    </div>
                @endisset
            @endif
        </div>
    </div>
@endif
