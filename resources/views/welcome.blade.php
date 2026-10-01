<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Berkah Mandiri Inventory') }} - Sistem Manajemen Stok Barang</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 font-sans antialiased selection:bg-indigo-600 selection:text-white">
        <div class="relative min-h-screen flex flex-col justify-between overflow-hidden">
            <!-- Navbar -->
            <header class="border-b border-slate-200/80 backdrop-blur bg-white/80 sticky top-0 z-50">
                <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Berkahventory" class="w-10 h-10 object-contain">
                        <div>
                            <span class="font-bold text-lg text-slate-900 tracking-tight">Berkah Mandiri Inventory</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                            Buka Dashboard
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Masuk ke Sistem
                        </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-6xl mx-auto px-6 py-16 flex-1 w-full">
                <!-- Hero section -->
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 font-medium mb-6">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sistem Siap Operasional
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900 mb-4">
                        Berkah Mandiri <span class="text-indigo-600">Inventory</span>
                    </h1>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Aplikasi pencatatan barang masuk, barang keluar, kartu stok otomatis, dan rekapitulasi laporan inventaris gudang berbasis web.
                    </p>

                    <div class="mt-8 flex items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Mulai Sekarang
                        </a>
                    </div>
                </div>

                <!-- Feature Badges -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                            <x-heroicon-o-cube class="w-5 h-5" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Master Data Lengkap</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pengelolaan barang, kategori, satuan kuantitas, dan direktori rekanan vendor supplier secara rapi.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Mutasi Masuk & Keluar</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pencatatan faktur barang masuk (Stock In) dan pengeluaran barang operasional (Stock Out) terintegrasi.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                            <x-heroicon-o-document-chart-bar class="w-5 h-5" />
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">Kartu Stok & Laporan</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Audit running balance kronologis per SKU barang serta rekapitulasi laporan siap cetak untuk pengujian.
                        </p>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 py-6 text-center text-xs text-slate-500 bg-white">
                Berkah Mandiri Inventory &bull; Sistem Informasi Stok Barang UMKM
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
