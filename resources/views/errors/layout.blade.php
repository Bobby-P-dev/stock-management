<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>@yield('title', 'Terjadi Kesalahan') - {{ config('app.name', 'Berkah Mandiri Inventory') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white flex flex-col justify-between p-4 sm:p-6 lg:p-8">
        
        {{-- Top Brand Navigation Bar --}}
        <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3 group transition">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/90 p-1.5 shadow-2xs flex items-center justify-center shrink-0 group-hover:border-blue-300 transition">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Berkah Mandiri" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition tracking-tight">
                        Berkah Mandiri Inventory
                    </span>
                    <p class="text-[11px] text-slate-400">Sistem Manajemen Persediaan &amp; Gudang</p>
                </div>
            </a>

            <div class="flex items-center gap-2">
                <a
                    href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                    class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Kembali Sebelumnya</span>
                </a>
            </div>
        </header>

        {{-- Main Error Content Container --}}
        <main class="w-full max-w-3xl mx-auto my-auto py-3 sm:py-5 flex flex-col items-center text-center">
            
            {{-- Illustration Area --}}
            <div class="relative w-full max-w-sm sm:max-w-md mb-3 flex items-center justify-center">
                @hasSection('image')
                    @yield('image')
                @else
                    <img
                        src="{{ asset('images/errors/404.png') }}"
                        alt="@yield('title', 'Error')"
                        class="w-full h-auto max-h-48 sm:max-h-56 object-contain filter drop-shadow-sm select-none pointer-events-none"
                    >
                @endif
            </div>

            {{-- Status Badge --}}
            <div class="mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold @yield('badge-class', 'bg-blue-50 text-blue-700 border border-blue-200/80')">
                    <span class="w-1.5 h-1.5 rounded-full @yield('badge-dot-class', 'bg-blue-600')"></span>
                    @yield('badge-text', 'Status Error')
                </span>
            </div>

            {{-- Title & Description --}}
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight max-w-lg mb-2.5">
                @yield('heading', 'Terjadi Kesalahan')
            </h1>

            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto mb-6">
                @yield('message', 'Halaman atau permintaan yang Anda cari tidak dapat diproses saat ini.')
            </p>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full max-w-sm">
                @hasSection('actions')
                    @yield('actions')
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-2xs hover:shadow transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Ke Dashboard Utama</span>
                    </a>

                    <button
                        type="button"
                        onclick="window.location.reload()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold shadow-2xs transition"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Muat Ulang Halaman</span>
                    </button>
                @endif
            </div>

        </main>

        {{-- Footer --}}
        <footer class="w-full max-w-5xl mx-auto py-3 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Berkah Mandiri Inventory. All rights reserved.
        </footer>

    </body>
</html>
