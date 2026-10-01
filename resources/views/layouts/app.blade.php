<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
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
    <body class="h-full bg-slate-50 text-slate-800 font-sans antialiased selection:bg-indigo-600 selection:text-white" x-data="{ sidebarOpen: false }">
        <div class="min-h-full flex">
            <!-- Mobile Sidebar Backdrop -->
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
                style="display: none;"
            ></div>

            <!-- Sidebar Navigation -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:flex"
            >
                <!-- Brand / Logo -->
                <div class="h-16 px-5 flex items-center gap-3 border-b border-slate-100">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Berkahventory" class="w-10 h-10 object-contain">
                    <div>
                        <div class="font-bold text-slate-900 tracking-tight leading-tight">Berkah Mandiri</div>
                        <div class="text-[10px] text-slate-500 font-medium">Inventory System</div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto">
                    <!-- Menu Utama -->
                    <div>
                        <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Utama</div>
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <x-heroicon-o-home class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" />
                            Dashboard
                        </a>
                    </div>

                    <!-- Master Data -->
                    <div>
                        <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Master Data</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('products.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-archive-box class="w-4 h-4 shrink-0 {{ request()->routeIs('products.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Data Barang
                            </a>

                            @if(auth()->user()->isAdmin())
                            <a
                                href="{{ route('categories.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-tag class="w-4 h-4 shrink-0 {{ request()->routeIs('categories.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Kategori
                            </a>

                            <a
                                href="{{ route('units.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('units.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-scale class="w-4 h-4 shrink-0 {{ request()->routeIs('units.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Satuan
                            </a>

                            <a
                                href="{{ route('suppliers.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('suppliers.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-truck class="w-4 h-4 shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Supplier
                            </a>

                            <a
                                href="{{ route('users.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-users class="w-4 h-4 shrink-0 {{ request()->routeIs('users.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Kelola Pengguna
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Transaksi Stok -->
                    <div>
                        <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Transaksi Stok</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('transactions.in') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('transactions.in') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 shrink-0 text-emerald-600" />
                                Barang Masuk (IN)
                            </a>

                            <a
                                href="{{ route('transactions.out') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('transactions.out') ? 'bg-rose-50 text-rose-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4 shrink-0 text-rose-600" />
                                Barang Keluar (OUT)
                            </a>

                            <a
                                href="{{ route('transactions.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-clipboard-document-list class="w-4 h-4 shrink-0 {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Riwayat Mutasi
                            </a>
                        </div>
                    </div>

                    <!-- Laporan & Audit -->
                    <div>
                        <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Laporan & Audit</div>
                        <div class="space-y-1">
                            <a
                                href="{{ route('opnames.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('opnames.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4 shrink-0 {{ request()->routeIs('opnames.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Stock Opname (Fisik)
                            </a>

                            <a
                                href="{{ route('stock-card') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('stock-card*') ? 'bg-amber-50 text-amber-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-table-cells class="w-4 h-4 shrink-0 text-amber-600" />
                                Kartu Stok (Stock Card)
                            </a>

                            <a
                                href="{{ route('reports.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                <x-heroicon-o-document-chart-bar class="w-4 h-4 shrink-0 {{ request()->routeIs('reports.*') ? 'text-indigo-600' : 'text-slate-400' }}" />
                                Laporan Mutasi & Stok
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- User profile footer -->
                <div class="p-3.5 border-t border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                                <div class="text-[10px] text-slate-500 capitalize">{{ auth()->user()->role ?? 'petugas' }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                title="Keluar dari sistem"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                            >
                                <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Header -->
                <header class="h-16 bg-white/90 border-b border-slate-200/80 backdrop-blur sticky top-0 z-30 px-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 lg:hidden"
                        >
                            <x-heroicon-o-bars-3 class="w-5 h-5" />
                        </button>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">
                            {{ $header ?? 'Berkah Mandiri Inventory' }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold {{ auth()->user()->isAdmin() ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->isAdmin() ? 'bg-indigo-600' : 'bg-emerald-600' }}"></span>
                            {{ strtoupper(auth()->user()->role ?? 'petugas') }}
                        </span>
                    </div>
                </header>

                <!-- Page Body -->
                <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
