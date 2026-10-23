<div class="w-full max-w-4xl mx-auto min-w-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center">
        
        {{-- Left Brand Area --}}
        <div class="lg:col-span-6 flex flex-col justify-center text-center lg:text-left">
            <div class="flex justify-center lg:justify-start mb-5">
                <img
                    src="{{ asset('images/logo-trimmed.png') }}"
                    alt="Logo Berkah Mandiri"
                    class="h-16 sm:h-20 lg:h-24 w-auto object-contain select-none"
                >
            </div>

            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900 leading-tight">
                Berkah Mandiri Inventory
            </h1>

            <p class="text-xs sm:text-sm text-slate-500 mt-2.5 leading-relaxed max-w-sm mx-auto lg:mx-0">
                Sistem manajemen inventaris untuk pencatatan barang masuk, barang keluar, dan pengawasan stok gudang.
            </p>

            <div class="mt-8 pt-5 border-t border-slate-200/60 hidden lg:block text-[11px] text-slate-400 font-medium">
                Portal Akses Pergudangan Internal
            </div>
        </div>

        {{-- Right Login Card --}}
        <div class="lg:col-span-6 w-full max-w-md mx-auto lg:max-w-none">
            <div class="relative overflow-hidden bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-sm">
                {{-- Top Loading Indicator --}}
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
                            class="w-full h-11 text-xs sm:text-sm font-semibold shadow-2xs hover:bg-blue-700 transition"
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
