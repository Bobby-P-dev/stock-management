@extends('errors.layout')

@section('title', '503 - Sistem Dalam Pemeliharaan')

@section('image')
    <img
        src="{{ asset('images/errors/500.png') }}"
        alt="503 Pemeliharaan Sistem"
        class="w-full h-full object-contain filter drop-shadow-sm select-none pointer-events-none"
    >
@endsection

@section('badge-class', 'bg-blue-50 text-blue-700 border border-blue-200/80')
@section('badge-dot-class', 'bg-blue-600 animate-pulse')
@section('badge-text', 'Status 503 • Pemeliharaan Sistem')

@section('heading', 'Sistem Sedang Dalam Perawatan')

@section('message', 'Kami sedang melakukan pemeliharaan rutin dan peningkatan performa sistem inventaris. Layanan akan segera aktif kembali beberapa saat lagi.')

@section('actions')
    <button
        type="button"
        onclick="window.location.reload()"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-2xs hover:shadow transition"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <span>Periksa Status (Muat Ulang)</span>
    </button>
@endsection
