@extends('errors.layout')

@section('title', '500 - Terjadi Gangguan Server')

@section('image')
    <img
        src="{{ asset('images/errors/500.png') }}"
        alt="500 Gangguan Server"
        class="w-full h-auto max-h-48 sm:max-h-56 object-contain filter drop-shadow-sm select-none pointer-events-none"
    >
@endsection

@section('badge-class', 'bg-rose-50 text-rose-700 border border-rose-200/80')
@section('badge-dot-class', 'bg-rose-600')
@section('badge-text', 'Error 500 • Gangguan Sistem')

@section('heading', 'Terjadi Gangguan pada Server')

@section('message', 'Sistem inventaris mengalami kendala internal tak terduga saat memproses data. Silakan muat ulang halaman atau coba beberapa saat lagi.')

@section('actions')
    <button
        type="button"
        onclick="window.location.reload()"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-2xs hover:shadow transition"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <span>Coba Muat Ulang</span>
    </button>

    <a
        href="{{ route('dashboard') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold shadow-2xs transition"
    >
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        <span>Ke Dashboard Utama</span>
    </a>
@endsection
