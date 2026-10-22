@props([
    'width' => 'w-44',
    'menuWidthPx' => 176,
    'menuHeightPx' => 96,
    'label' => 'Opsi lainnya',
])

<div
    x-data="{
        menuOpen: false,
        menuTop: 0,
        menuLeft: 0,
        toggleMenu(event) {
            if (this.menuOpen) {
                this.menuOpen = false;
                return;
            }
            const rect = event.currentTarget.getBoundingClientRect();
            const menuHeight = {{ (int) $menuHeightPx }};
            const menuWidth = {{ (int) $menuWidthPx }};
            const spaceBelow = window.innerHeight - rect.bottom;
            this.menuTop = spaceBelow < menuHeight ? Math.max(8, rect.top - menuHeight - 6) : (rect.bottom + 6);
            this.menuLeft = Math.max(8, Math.min(window.innerWidth - menuWidth - 8, rect.right - menuWidth));
            this.menuOpen = true;
        },
        closeMenu() {
            this.menuOpen = false;
        }
    }"
    @click.outside="closeMenu()"
    @keydown.escape.window="closeMenu()"
    @scroll.window="if (menuOpen) closeMenu()"
    @resize.window="if (menuOpen) closeMenu()"
    {{ $attributes->merge(['class' => 'relative inline-block text-left']) }}
>
    @if (isset($trigger))
        <div @click="toggleMenu($event)">
            {{ $trigger }}
        </div>
    @else
        <button
            type="button"
            @click="toggleMenu($event)"
            :aria-expanded="menuOpen"
            aria-haspopup="true"
            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white hover:bg-slate-50 hover:text-slate-800 border border-slate-200 shadow-2xs transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
            title="{{ $label }}"
            aria-label="{{ $label }}"
        >
            <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
        </button>
    @endif

    <div
        x-show="menuOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="menu"
        aria-orientation="vertical"
        :style="`top: ${menuTop}px; left: ${menuLeft}px;`"
        class="fixed {{ $width }} bg-white border border-slate-200 rounded-xl shadow-lg p-1 z-50 text-left"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
