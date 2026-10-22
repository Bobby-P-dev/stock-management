@props([
    'options' => [],
    'placeholder' => 'Pilih...',
    'searchPlaceholder' => 'Cari...',
    'emptyTitle' => 'No results',
    'emptyMessage' => 'Tidak ada data yang cocok dengan nama tersebut.',
    'limit' => 100,
    'required' => false,
    'disabled' => false,
    'dropUp' => false,
    'id' => null,
    'name' => null,
    'label' => null,
    'error' => null,
    'hint' => null,
])

@php
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $fieldName = $name ?? $wireModel;
    $selectId = $id ?? ($fieldName ? 'select-' . str_replace(['.', '_'], '-', $fieldName) : 'select-' . uniqid());
    $errorMessage = $error ?? ($fieldName && isset($errors) ? $errors->first($fieldName) : null);
    $errorId = $selectId . '-error';
    $hintId = $selectId . '-hint';
    $describedBy = collect([
        $errorMessage ? $errorId : null,
        (! $errorMessage && $hint) ? $hintId : null,
    ])->filter()->implode(' ') ?: null;

    $normalizedOptions = collect($options)->map(function ($item, $key) {
        if (is_object($item)) {
            $value = $item->id ?? $item->value ?? $key;
            if (isset($item->sku, $item->name)) {
                $label = $item->sku . ' - ' . $item->name;
                if (isset($item->current_stock, $item->unit->symbol)) {
                    $label .= " (Sisa: {$item->current_stock} {$item->unit->symbol})";
                } elseif (isset($item->unit->symbol)) {
                    $label .= " ({$item->unit->symbol})";
                }
            } elseif (isset($item->code, $item->name)) {
                $label = $item->code . ' - ' . $item->name;
            } elseif (isset($item->name, $item->symbol)) {
                $label = $item->name . ' (' . $item->symbol . ')';
            } elseif (isset($item->name)) {
                $label = $item->name;
            } elseif (isset($item->label)) {
                $label = $item->label;
            } else {
                $label = (string) $item;
            }
            return ['value' => (string) $value, 'label' => (string) $label];
        } elseif (is_array($item)) {
            $value = $item['value'] ?? $item['id'] ?? $key;
            $label = $item['label'] ?? $item['name'] ?? (string) $value;
            return ['value' => (string) $value, 'label' => (string) $label];
        } else {
            return ['value' => (string) $key, 'label' => (string) $item];
        }
    })->values()->toArray();
@endphp

<div
    x-data="{
        open: false,
        dropUp: {{ $dropUp ? 'true' : 'false' }},
        search: '',
        value: null,
        limit: {{ $limit }},
        highlightIndex: 0,
        options: @js($normalizedOptions),
        init() {
            this.$watch('options', () => {
                this.highlightIndex = 0;
            });
        },
        toggle() {
            if ({{ $disabled ? 'true' : 'false' }}) return;
            this.open = !this.open;
            if (this.open) {
                this.search = '';
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        },
        get selectedLabel() {
            if (this.value === null || this.value === undefined) {
                return '';
            }
            let found = this.options.find(o => String(o.value) === String(this.value));
            return found ? found.label : '';
        },
        get filteredOptions() {
            if (!this.search || !this.search.trim()) {
                return this.options.slice(0, this.limit);
            }
            let q = this.search.toLowerCase().trim();
            return this.options.filter(o => o.label.toLowerCase().includes(q)).slice(0, this.limit);
        },
        select(val) {
            this.value = val;
            this.search = '';
            this.open = false;
            this.$nextTick(() => {
                if (this.$refs.hiddenInput) {
                    this.$refs.hiddenInput.value = val;
                    this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            this.$dispatch('input', val);
            this.$dispatch('change', val);
        },
        clear() {
            this.value = '';
            this.search = '';
            this.open = false;
            this.$nextTick(() => {
                if (this.$refs.hiddenInput) {
                    this.$refs.hiddenInput.value = '';
                    this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            this.$dispatch('input', '');
            this.$dispatch('change', '');
        },
        selectHighlighted() {
            if (this.filteredOptions.length > 0 && this.highlightIndex >= 0 && this.highlightIndex < this.filteredOptions.length) {
                this.select(this.filteredOptions[this.highlightIndex].value);
            }
        },
        moveHighlight(direction) {
            if (!this.open) {
                this.open = true;
                return;
            }
            let max = this.filteredOptions.length - 1;
            if (max < 0) return;
            this.highlightIndex += direction;
            if (this.highlightIndex < 0) this.highlightIndex = max;
            if (this.highlightIndex > max) this.highlightIndex = 0;
        }
    }"
    x-modelable="value"
    x-effect="options = @js($normalizedOptions)"
    {{ $attributes->whereDoesntStartWith('class') }}
    @click.outside="open = false; search = ''"
    @keydown.escape.window="if (open) { open = false; search = ''; $event._dropdownClosed = true; $refs.triggerBtn?.focus(); }"
    class="relative {{ $attributes->get('class') }}"
    :class="open ? 'z-50' : 'z-10'"
>
    <!-- Hidden input for standard form/Livewire capture -->
    <input type="hidden" x-ref="hiddenInput" @if ($fieldName) name="{{ $fieldName }}" @endif :value="value">

    @if ($label)
        <x-ui.label :for="$selectId" :required="$required">
            {{ $label }}
        </x-ui.label>
    @endif

    <!-- Trigger Button -->
    <button
        id="{{ $selectId }}"
        x-ref="triggerBtn"
        type="button"
        @if ($disabled) disabled @endif
        aria-haspopup="listbox"
        :aria-expanded="open ? 'true' : 'false'"
        aria-invalid="{{ $errorMessage ? 'true' : 'false' }}"
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @click="toggle()"
        class="w-full h-[42px] px-3.5 py-2 rounded-xl bg-white border {{ $errorMessage ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 hover:border-slate-300 focus:border-blue-600 focus:ring-blue-600/20' }} text-slate-900 text-xs font-medium flex items-center justify-between gap-2 shadow-2xs cursor-pointer focus:outline-none focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed transition"
    >
        <span class="truncate" :class="selectedLabel ? 'text-slate-800 font-medium' : 'text-slate-400 font-normal'" x-text="selectedLabel || '{{ $placeholder }}'">
        </span>
        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu / Popover -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-98"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-98"
        class="absolute z-50 left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden"
        :class="dropUp ? 'bottom-full mb-1.5' : 'top-full mt-1.5'"
        style="display: none;"
    >
        <!-- Search Input Header -->
        <div class="px-3 py-2 border-b border-slate-200 bg-slate-50/70 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                x-ref="searchInput"
                type="text"
                x-model="search"
                @keydown.arrow-down.prevent="moveHighlight(1)"
                @keydown.arrow-up.prevent="moveHighlight(-1)"
                @keydown.enter.prevent="selectHighlighted()"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none bg-transparent"
                autocomplete="off"
            >
            <button
                type="button"
                x-show="search.length > 0"
                @click.stop="search = ''; $refs.searchInput.focus()"
                class="text-slate-400 hover:text-slate-600 p-0.5 shrink-0 transition cursor-pointer"
                title="Hapus pencarian"
                style="display: none;"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Options List (Limit to 5 items) -->
        <div class="max-h-56 overflow-y-auto">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <button
                    type="button"
                    @click="select(opt.value)"
                    @mouseenter="highlightIndex = idx"
                    class="w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-100 transition-colors flex items-center justify-between gap-2 cursor-pointer"
                    :class="{
                        'bg-blue-50 text-blue-700 font-semibold': String(opt.value) === String(value),
                        'bg-slate-50': highlightIndex === idx && String(opt.value) !== String(value)
                    }"
                >
                    <span class="truncate" x-text="opt.label"></span>
                    <span x-show="String(opt.value) === String(value)" class="text-blue-600 text-xs font-bold shrink-0">✓</span>
                </button>
            </template>

            <!-- Empty State -->
            <div x-show="filteredOptions.length === 0" class="py-6 px-4 text-center">
                <svg class="w-6 h-6 mx-auto mb-1.5 text-slate-300 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <div class="font-semibold text-slate-700 text-xs">
                    {{ $emptyTitle }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    {{ $emptyMessage }}
                </div>
            </div>
        </div>
    </div>

    @if ($errorMessage)
        <x-ui.field-error :id="$errorId" :messages="$errorMessage" />
    @elseif ($hint)
        <p id="{{ $hintId }}" class="mt-1.5 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif
</div>