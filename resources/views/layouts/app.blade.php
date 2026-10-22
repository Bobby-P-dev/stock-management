<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'Berkah Mandiri Inventory') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    @php
        $currentUser = auth()->user();
        $userName = $currentUser->name ?? 'Pengguna';
        $userRoleLabel = $currentUser?->isAdmin() ? 'Administrator' : 'Petugas Gudang';
        $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
        $userInitials = strtoupper(
            substr($nameParts[0] ?? 'U', 0, 1) .
            (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
        );

        $breadcrumbs = match (true) {
            request()->routeIs('dashboard') => [
                ['label' => 'Dashboard'],
            ],
            request()->routeIs('products.*') => [
                ['label' => 'Master Data'],
                ['label' => 'Data Barang', 'url' => route('products.index')],
            ],
            request()->routeIs('categories.*') => [
                ['label' => 'Master Data'],
                ['label' => 'Kategori', 'url' => route('categories.index')],
            ],
            request()->routeIs('units.*') => [
                ['label' => 'Master Data'],
                ['label' => 'Satuan', 'url' => route('units.index')],
            ],
            request()->routeIs('suppliers.*') => [
                ['label' => 'Master Data'],
                ['label' => 'Supplier', 'url' => route('suppliers.index')],
            ],
            request()->routeIs('users.*') => [
                ['label' => 'Master Data'],
                ['label' => 'Kelola Pengguna', 'url' => route('users.index')],
            ],
            request()->routeIs('transactions.in') => [
                ['label' => 'Transaksi Stok'],
                ['label' => 'Barang Masuk', 'url' => route('transactions.in')],
            ],
            request()->routeIs('transactions.out') => [
                ['label' => 'Transaksi Stok'],
                ['label' => 'Barang Keluar', 'url' => route('transactions.out')],
            ],
            request()->routeIs('transactions.show') => [
                ['label' => 'Transaksi Stok'],
                ['label' => 'Riwayat Mutasi', 'url' => route('transactions.index')],
                ['label' => 'Detail Transaksi'],
            ],
            request()->routeIs('transactions.*') => [
                ['label' => 'Transaksi Stok'],
                ['label' => 'Riwayat Mutasi', 'url' => route('transactions.index')],
            ],
            request()->routeIs('opnames.create') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Stock Opname', 'url' => route('opnames.index')],
                ['label' => 'Catat Opname'],
            ],
            request()->routeIs('opnames.edit') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Stock Opname', 'url' => route('opnames.index')],
                ['label' => 'Edit Opname'],
            ],
            request()->routeIs('opnames.show') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Stock Opname', 'url' => route('opnames.index')],
                ['label' => 'Rincian Opname'],
            ],
            request()->routeIs('opnames.*') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Stock Opname', 'url' => route('opnames.index')],
            ],
            request()->routeIs('stock-card*') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Kartu Stok', 'url' => route('stock-card')],
            ],
            request()->routeIs('reports.*') => [
                ['label' => 'Laporan & Audit'],
                ['label' => 'Laporan Mutasi & Stok', 'url' => route('reports.index')],
            ],
            default => [
                ['label' => $header ?? 'Dashboard'],
            ],
        };
    @endphp
    <body
        class="min-h-full bg-[#F8FAFC] text-[#0F172A] font-sans antialiased selection:bg-[#2563EB] selection:text-white"
        x-data="{
            sidebarOpen: false,
            accountDropdownOpen: false,
            confirmLogout() {
                this.accountDropdownOpen = false;
                window.SwalConfirm({
                    title: 'Keluar dari Sistem?',
                    text: 'Sesi kerja Anda akan diakhiri dan Anda perlu login kembali untuk mengakses inventaris.',
                    icon: 'question',
                    confirmText: 'Ya, Keluar',
                    cancelText: 'Batal',
                    variant: 'rose',
                    onConfirm: () => this.$refs.logoutForm.submit()
                });
            }
        }"
        @keydown.escape.window="accountDropdownOpen = false; sidebarOpen = false"
    >
        <form method="POST" action="{{ route('logout') }}" x-ref="logoutForm" class="hidden">
            @csrf
        </form>

        <div class="min-h-screen flex">
            {{-- Mobile Sidebar Backdrop --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden print:hidden"
                @click="sidebarOpen = false"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 w-[240px] shrink-0 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:flex print:hidden"
            >
                {{-- Brand Header --}}
                <div class="h-14 px-4 flex items-center gap-2.5 border-b border-slate-200 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Berkahventory" class="w-8 h-8 object-contain shrink-0">
                    <div class="min-w-0">
                        <div class="text-sm font-bold text-slate-900 tracking-tight leading-tight truncate">Berkah Mandiri</div>
                        <div class="text-[11px] text-slate-500 font-medium leading-tight">Inventory System</div>
                    </div>
                </div>

                {{-- Navigation Links --}}
                <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
                    <div>
                        <div class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Utama</div>
                        <a
                            href="{{ route('dashboard') }}"
                            class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <x-heroicon-o-home class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div>
                        <div class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Master Data</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('products.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-archive-box class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('products.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Data Barang</span>
                            </a>

                            @if($currentUser?->isAdmin())
                            <a
                                href="{{ route('categories.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-tag class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('categories.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Kategori</span>
                            </a>

                            <a
                                href="{{ route('units.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('units.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-scale class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('units.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Satuan</span>
                            </a>

                            <a
                                href="{{ route('suppliers.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('suppliers.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-truck class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Supplier</span>
                            </a>

                            <a
                                href="{{ route('users.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-users class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('users.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Kelola Pengguna</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Transaksi Stok</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('transactions.in') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('transactions.in') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('transactions.in') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Barang Masuk (IN)</span>
                            </a>

                            <a
                                href="{{ route('transactions.out') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('transactions.out') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-arrow-up-tray class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('transactions.out') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Barang Keluar (OUT)</span>
                            </a>

                            <a
                                href="{{ route('transactions.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-clipboard-document-list class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Riwayat Mutasi</span>
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Laporan & Audit</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('opnames.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('opnames.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-clipboard-document-check class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('opnames.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Stock Opname (Fisik)</span>
                            </a>

                            <a
                                href="{{ route('stock-card') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('stock-card*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-table-cells class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('stock-card*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Kartu Stok (Stock Card)</span>
                            </a>

                            <a
                                href="{{ route('reports.index') }}"
                                class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] leading-5 transition-colors duration-150 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-document-chart-bar class="w-[18px] h-[18px] shrink-0 {{ request()->routeIs('reports.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                                <span>Laporan Mutasi & Stok</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>

            {{-- Main Content Shell --}}
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Topbar --}}
                <header class="h-14 bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 flex items-center justify-between gap-3 print:hidden">
                    {{-- Left: Mobile Menu Trigger + Dynamic Breadcrumb --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            class="p-1.5 -ml-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 lg:hidden shrink-0 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                            aria-label="Buka menu navigasi"
                        >
                            <x-heroicon-o-bars-3 class="w-5 h-5" />
                        </button>

                        <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[13px] min-w-0 overflow-hidden">
                            @foreach($breadcrumbs as $index => $crumb)
                                @if($index > 0)
                                    <span class="text-slate-300 select-none shrink-0">/</span>
                                @endif

                                @if($loop->last)
                                    <span class="font-semibold text-slate-900 truncate">{{ $crumb['label'] }}</span>
                                @elseif(!empty($crumb['url']))
                                    <a href="{{ $crumb['url'] }}" class="font-medium text-slate-500 hover:text-slate-900 transition-colors truncate">
                                        {{ $crumb['label'] }}
                                    </a>
                                @else
                                    <span class="font-medium text-slate-500 truncate">{{ $crumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                    </div>

                    {{-- Right: Single Header User Dropdown --}}
                    <div class="relative flex items-center shrink-0" @click.outside="accountDropdownOpen = false">
                        <button
                            type="button"
                            @click="accountDropdownOpen = !accountDropdownOpen"
                            :aria-expanded="accountDropdownOpen"
                            aria-haspopup="true"
                            class="inline-flex items-center gap-2 py-1 pl-1.5 pr-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1"
                        >
                            <span class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-[11px] font-semibold shrink-0">
                                {{ $userInitials }}
                            </span>
                            <span class="text-[13px] font-medium text-slate-700 max-w-[140px] truncate">
                                {{ $userName }}
                            </span>
                            <x-heroicon-o-chevron-down
                                class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-150"
                                ::class="accountDropdownOpen ? 'rotate-180 text-slate-600' : ''"
                            />
                        </button>

                        <div
                            x-show="accountDropdownOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            role="menu"
                            aria-orientation="vertical"
                            class="absolute right-0 top-full mt-2 w-[232px] bg-white border border-slate-200 rounded-xl shadow-md p-1.5 z-50"
                            style="display: none;"
                        >
                            <div class="px-3 py-2.5">
                                <p class="text-[13px] font-semibold text-slate-900 truncate">{{ $userName }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 capitalize">{{ $userRoleLabel }}</p>
                            </div>

                            <div class="my-1 border-t border-slate-100"></div>

                            <button
                                type="button"
                                role="menuitem"
                                @click="confirmLogout()"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-rose-600 hover:bg-rose-50/80 transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                            >
                                <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4 shrink-0" />
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="flex-1 p-4 sm:p-6 bg-[#F8FAFC] print:p-0 print:bg-white">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if(session('success') || session('error') || session('warning'))
        <div
            x-data
            x-init="
                const runFlash = () => {
                    if (typeof window.SwalToast !== 'function') {
                        setTimeout(runFlash, 50);
                        return;
                    }
                    @if(session('success'))
                        window.SwalToast({
                            icon: 'success',
                            title: 'Berhasil',
                            text: @js(session('success')),
                        });
                    @endif
                    @if(session('error'))
                        window.SwalAlert({
                            icon: 'error',
                            title: 'Gagal Memproses',
                            text: @js(session('error')),
                        });
                    @endif
                    @if(session('warning'))
                        window.SwalAlert({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: @js(session('warning')),
                        });
                    @endif
                };
                runFlash();
            "
            class="hidden"
        ></div>
        @endif

        @livewireScripts
    </body>
</html>
