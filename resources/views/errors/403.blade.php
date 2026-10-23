@extends('errors.layout')

@section('title', '403 - Akses Ditolak')

@section('image')
    <img
        src="{{ asset('images/errors/403.png') }}"
        alt="403 Akses Ditolak"
        class="w-full h-auto max-h-48 sm:max-h-56 object-contain filter drop-shadow-sm select-none pointer-events-none"
    >
@endsection

@section('badge-class', 'bg-amber-50 text-amber-700 border border-amber-200/80')
@section('badge-dot-class', 'bg-amber-600')
@section('badge-text', 'Error 403 • Akses Dibatasi')

@section('heading', 'Akses Terbatas atau Ditolak')

@section('message', 'Akun Anda tidak memiliki hak akses otorisasi yang cukup untuk membuka halaman atau fitur gudang ini. Silakan hubungi Administrator jika membutuhkan wewenang akses.')

@section('actions')
    <a
        href="{{ route('dashboard') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-2xs hover:shadow transition"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        <span>Ke Dashboard Utama</span>
    </a>

    <a
        href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold shadow-2xs transition"
    >
        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        <span>Kembali Sebelumnya</span>
    </a>
@endsection
