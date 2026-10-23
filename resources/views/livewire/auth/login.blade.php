<div class="w-full max-w-5xl mx-auto min-w-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 xl:gap-16 items-center">
        
        {{-- Left Brand & Value Proposition Panel --}}
        <div class="lg:col-span-6 xl:col-span-7 flex flex-col justify-center">
            {{-- Brand Identity --}}
            <div class="flex items-center gap-3.5 mb-4">
                <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-2xl bg-white border border-slate-200/90 p-2.5 shadow-2xs flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Berkah Mandiri" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                        Inventory Management System
                    </span>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        Berkah Mandiri Inventory
                    </h1>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Sistem Informasi Manajemen Barang Masuk &amp; Barang Keluar
            </p>

            <p class="text-xs sm:text-sm text-slate-600 mt-3 leading-relaxed max-w-lg hidden sm:block">
                Solusi manajemen inventaris terpadu untuk pencatatan mutasi barang, kontrol stok minimum secara real-time, dan audit aset gudang yang akurat.
            </p>

            {{-- Feature / Value Points (Desktop & Tablet) --}}
            <div class="hidden sm:flex flex-col gap-3.5 mt-6 pt-6 border-t border-slate-200/70">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 text-blue-600 mt-0.5">
                        <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 leading-tight">Monitoring Mutasi Cepat &amp; Rapi</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Pencatatan arus barang masuk dan keluar dengan barcode scanner dan riwayat transaksi terinci.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 text-emerald-600 mt-0.5">
                        <x-heroicon-o-shield-check class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 leading-tight">Kontrol Stok &amp; Peringatan Otomatis</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Peringatan otomatis saat stok menipis serta klasifikasi perputaran barang fast, medium, dan slow moving.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center shrink-0 text-indigo-600 mt-0.5">
                        <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 leading-tight">Valuasi Aset &amp; Rekonsiliasi Terpadu</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Kalkulasi nilai rupiah modal stok riil, kartu stok transparan, dan laporan siap ekspor maupun cetak.</p>
                    </div>
                </div>
            </div>

            {{-- Trust Badges (Desktop) --}}
            <div class="hidden lg:flex items-center gap-5 mt-6 pt-5 border-t border-slate-200/60 text-[11px] text-slate-500 font-medium">
                <div class="flex items-center gap-1.5">
                    <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Enkripsi Sesi Aman</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-heroicon-o-cpu-chip class="w-3.5 h-3.5 text-blue-600" />
                    <span>Real-Time Sync</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-heroicon-o-document-check class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Audit Terverifikasi</span>
                </div>
            </div>
        </div>

        {{-- Right Login Card --}}
        <div class="lg:col-span-6 xl:col-span-5 w-full max-w-md mx-auto lg:max-w-none">
            <div class="relative overflow-hidden bg-white border border-slate-200/90 rounded-2xl sm:rounded-[20px] p-6 sm:p-8 lg:p-9 shadow-lg shadow-slate-200/40">
                {{-- Subtle Top Progress Loading Bar --}}
                <div
                    wire:loading
                    wire:target="login"
                    class="absolute inset-x-0 top-0 h-1 bg-blue-600 animate-pulse z-20"
                ></div>

                <div class="mb-5 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Masuk ke Akun</h2>
                    <p class="text-xs text-slate-500 mt-1">Silakan masukkan kredensial untuk mengakses sistem.</p>
                </div>

                <form wire:submit="login" class="space-y-4">
                    {{-- Alamat Email --}}
                    <x-ui.input
                        wire:model="email"
                        type="email"
                        id="email"
                        name="email"
                        label="Alamat Email"
                        placeholder="nama@perusahaan.com"
                        required
                        :show-required-indicator="false"
                        autofocus
                        wire:loading.attr="disabled"
                        wire:target="login"
                        class="h-11 text-xs sm:text-sm"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-envelope class="w-4 h-4" />
                        </x-slot:icon>
                    </x-ui.input>

                    {{-- Kata Sandi --}}
                    <x-ui.input
                        wire:model="password"
                        type="password"
                        id="password"
                        name="password"
                        label="Kata Sandi"
                        placeholder="••••••••"
                        required
                        :show-required-indicator="false"
                        wire:loading.attr="disabled"
                        wire:target="login"
                        class="h-11 text-xs sm:text-sm"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-lock-closed class="w-4 h-4" />
                        </x-slot:icon>
                    </x-ui.input>

                    {{-- Ingat Sesi Saya Checkbox --}}
                    <div class="pt-0.5">
                        <x-ui.checkbox
                            wire:model="remember"
                            id="remember"
                            name="remember"
                            label="Ingat sesi saya"
                            wire:loading.attr="disabled"
                            wire:target="login"
                        />
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            size="md"
                            loadingTarget="login"
                            loadingText="Sedang Masuk..."
                            class="w-full h-11 sm:h-12 text-xs sm:text-sm font-semibold shadow-sm hover:shadow-md transition-all"
                        >
                            <x-slot:icon>
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 mr-1.5" />
                            </x-slot:icon>
                            Masuk ke Sistem
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- Footer Info --}}
            <div class="text-center mt-5 text-[11px] text-slate-400">
                &copy; {{ date('Y') }} Berkah Mandiri Inventory. All rights reserved.
            </div>
        </div>

    </div>
</div>
