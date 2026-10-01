<div class="w-full max-w-md">
    <!-- Header Logo & Title -->
    <div class="text-center mb-8">
        <div class="flex justify-center mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Berkahventory" class="w-28 h-28 object-contain">
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Berkah Mandiri Inventory</h1>
        <p class="text-xs text-slate-500 mt-1">Sistem Informasi Manajemen Barang Masuk & Barang Keluar</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-xl shadow-slate-200/50">
        <h2 class="text-lg font-bold text-slate-900 mb-1">Masuk ke Akun</h2>
        <p class="text-xs text-slate-500 mb-6">Silakan masukkan kredensial untuk mengakses sistem.</p>

        <form wire:submit="login" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                    </div>
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        required
                        autofocus
                        placeholder="nama@perusahaan.com"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                </div>
                @error('email')
                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                    </div>
                    <input
                        wire:model="password"
                        type="password"
                        id="password"
                        required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                </div>
                @error('password')
                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-600">Ingat sesi saya</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition duration-150 flex items-center justify-center gap-2 cursor-pointer"
            >
                <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                    Masuk ke Sistem
                </span>
                <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Memverifikasi...
                </span>
            </button>
        </form>
    </div>
</div>
