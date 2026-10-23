<div
    class="min-h-full flex flex-col justify-between p-3 sm:p-5 max-w-lg mx-auto w-full relative"
    x-data="remoteScannerEngine()"
    x-init="initScanner()"
>
    @if(!$isValid)
        {{-- Sesi Tidak Valid / Berakhir --}}
        <div class="my-auto text-center p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl">
            <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8" />
            </div>
            <h2 class="text-lg font-bold text-white mb-1.5">Sesi Scanner Tidak Ditemukan</h2>
            <p class="text-xs text-slate-400 leading-relaxed mb-6">
                Sesi pairing ini tidak valid atau telah berakhir (melewati batas waktu 2 jam).
                Silakan scan ulang QR Code terbaru yang tampil di layar laptop Anda.
            </p>
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition"
            >
                Buka Halaman Utama
            </a>
        </div>
    @else
        {{-- Header Status Bar --}}
        <header class="flex items-center justify-between gap-3 bg-slate-900/90 backdrop-blur-md border border-slate-800/80 rounded-2xl px-4 py-3 shrink-0 shadow-lg z-20">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 shrink-0">
                    <x-heroicon-o-qr-code class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-bold text-white tracking-tight truncate">
                            Terhubung ke Laptop
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">
                        {{ $sessionData['user_name'] ?? 'Petugas' }} &bull;
                        <span class="font-semibold text-blue-400">
                            {{ match($sessionData['type'] ?? '') { 'IN' => 'Barang Masuk', 'OUT' => 'Barang Keluar', 'OPNAME' => 'Stock Opname', default => 'Inventaris' } }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                {{-- Sound Mute/Unmute Toggle --}}
                <button
                    type="button"
                    @click="isMuted = !isMuted"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-semibold border transition shadow-xs"
                    :class="isMuted ? 'bg-slate-800 text-slate-500 border-slate-700' : 'bg-slate-800 text-emerald-400 border-slate-700 hover:bg-slate-700'"
                    :title="isMuted ? 'Suara Senyap (Klik untuk aktifkan)' : 'Suara Aktif (Klik untuk matikan)'"
                >
                    <x-heroicon-o-speaker-wave x-show="!isMuted" class="w-4 h-4" />
                    <x-heroicon-o-speaker-x-mark x-show="isMuted" class="w-4 h-4" />
                </button>

                {{-- Flashlight (Torch) Toggle --}}
                <button
                    type="button"
                    x-show="hasTorch"
                    @click="toggleTorch()"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold border transition shadow-xs"
                    :class="torchOn ? 'bg-amber-500 text-slate-950 border-amber-400 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'"
                >
                    <x-heroicon-o-bolt class="w-4 h-4" />
                    <span x-text="torchOn ? 'Senter ON' : 'Senter'"></span>
                </button>
            </div>
        </header>

        {{-- Notifikasi Transaksi Tersimpan dari Laptop --}}
        @if(!empty($sessionData['last_transaction']))
            <div class="mt-2.5 px-3.5 py-2 rounded-xl bg-emerald-950/80 border border-emerald-700/80 text-emerald-300 text-xs font-medium flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-1.5 truncate">
                    <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-400 shrink-0" />
                    <span class="truncate">Transaksi <strong>{{ $sessionData['last_transaction']['transaction_no'] }}</strong> tersimpan di laptop!</span>
                </div>
                <span class="text-[10px] text-emerald-400 font-mono shrink-0 ml-2">{{ $sessionData['last_transaction']['time'] }}</span>
            </div>
        @endif

        {{-- Camera Viewport Section --}}
        <main class="my-3 flex-1 flex flex-col justify-center relative min-h-[340px]">
            <div class="w-full aspect-[3/4] sm:aspect-square rounded-3xl bg-black border-2 border-slate-800 overflow-hidden relative shadow-2xl flex items-center justify-center">
                {{-- HTML5 Reader Target Container --}}
                <div wire:ignore id="remote-barcode-reader" class="w-full h-full"></div>

                {{-- Loading Spinner --}}
                <div x-show="isInitializing" class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center gap-3 p-6 text-center z-10">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-medium text-slate-400">Menghubungkan kamera HP...</span>
                </div>

                {{-- Camera Error State --}}
                <div x-show="errorMessage" class="absolute inset-x-4 top-4 p-4 rounded-2xl bg-rose-950/90 border border-rose-800 text-rose-200 text-xs text-left z-20 backdrop-blur-md">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" />
                        <div>
                            <span class="font-bold text-white block" x-text="errorMessage"></span>
                            <span class="text-[11px] text-rose-300 mt-1 block">
                                Izinkan akses kamera pada peramban Anda. Bila di jaringan lokal, pastikan menggunakan koneksi HTTPS atau tunnel.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Viewfinder Reticle & Laser line overlay --}}
                <div x-show="!isInitializing && !errorMessage" class="absolute inset-6 pointer-events-none flex flex-col items-center justify-center">
                    <div class="w-full max-w-[280px] h-48 border-2 border-dashed border-blue-400/80 rounded-2xl relative overflow-hidden flex items-center justify-center">
                        <div class="absolute w-full h-0.5 bg-rose-500 shadow-[0_0_12px_rgba(244,63,94,1)] animate-pulse"></div>
                        <span class="text-[10px] text-blue-200 bg-slate-950/70 px-2.5 py-1 rounded-md backdrop-blur-xs font-semibold tracking-wide">
                            Posisikan Barcode di Sini
                        </span>
                    </div>
                </div>

                {{-- Real-time Scanned Overlay Toast on Phone --}}
                <div
                    x-show="flashMessage"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    class="absolute top-4 inset-x-4 bg-emerald-500 text-slate-950 font-bold px-3.5 py-2.5 rounded-xl text-xs text-center shadow-2xl z-30 flex items-center justify-center gap-2"
                >
                    <x-heroicon-o-check-circle class="w-4 h-4 text-slate-950 shrink-0" />
                    <span x-text="flashMessage" class="truncate"></span>
                </div>
                {{-- Zoom Controls Pills (1x, 2x, 3x) --}}
                <div x-show="!isInitializing && !errorMessage" class="absolute bottom-3 inset-x-0 flex items-center justify-center z-20 pointer-events-auto">
                    <div class="inline-flex items-center p-1 rounded-full bg-slate-950/80 backdrop-blur-md border border-slate-700 shadow-xl gap-1">
                        <button
                            type="button"
                            @click="setZoom(1)"
                            class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                            :class="zoomLevel === 1 ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                        >
                            1x
                        </button>
                        <button
                            type="button"
                            @click="setZoom(2)"
                            class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                            :class="zoomLevel === 2 ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                        >
                            2x
                        </button>
                        <button
                            type="button"
                            @click="setZoom(3)"
                            class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                            :class="zoomLevel === 3 ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                        >
                            3x
                        </button>
                    </div>
                </div>
            </div>
        </main>

        {{-- Bottom Drawer: Stats & Recent Scans --}}
        <footer class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shrink-0 space-y-2.5 shadow-xl">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400 font-medium">Total Terkirim ke Laptop:</span>
                <span class="px-2 py-0.5 rounded-lg bg-blue-600/20 border border-blue-500/40 text-blue-300 font-bold text-xs">
                    {{ $scanCount }} Item
                </span>
            </div>

            @if(!empty($recentScans))
                <div class="border-t border-slate-800/80 pt-2 space-y-1.5">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">
                        Riwayat Terakhir Dipindai
                    </span>
                    <div class="space-y-1">
                        @foreach(array_slice($recentScans, 0, 3) as $scan)
                            <div class="flex items-center justify-between text-[11px] bg-slate-950/60 border border-slate-800/60 rounded-lg px-2.5 py-1.5">
                                <span class="font-medium text-slate-200 truncate max-w-[190px]">
                                    {{ $scan['name'] }}
                                </span>
                                <span class="font-mono text-[10px] text-slate-400">
                                    {{ $scan['code'] }} &bull; {{ $scan['time'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Fallback Manual Input on Mobile --}}
            <div class="pt-1">
                <form @submit.prevent="submitManual()" class="flex items-center gap-2">
                    <input
                        type="text"
                        x-model="manualInput"
                        placeholder="Ketik barcode jika tidak fokus..."
                        class="w-full text-xs font-mono rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
                    />
                    <button
                        type="submit"
                        class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shrink-0"
                    >
                        Kirim
                    </button>
                </form>
            </div>
        </footer>
    @endif
</div>

<script>
function remoteScannerEngine() {
    return {
        html5QrCode: null,
        isInitializing: false,
        hasTorch: false,
        torchOn: false,
        errorMessage: '',
        flashMessage: '',
        manualInput: '',
        cooldown: false,
        isMuted: false,
        zoomLevel: 1,
        hasZoom: false,

        initScanner() {
            @if($isValid)
                this.$nextTick(() => {
                    this.startCamera();
                });
            @endif
        },

        async startCamera() {
            this.isInitializing = true;
            this.errorMessage = '';

            try {
                if (!window.Html5Qrcode) {
                    throw new Error('Library Html5Qrcode belum termuat.');
                }

                if (this.html5QrCode) {
                    try {
                        if (this.html5QrCode.isScanning) await this.html5QrCode.stop();
                    } catch(e) {}
                }

                this.html5QrCode = new window.Html5Qrcode('remote-barcode-reader');

                const config = {
                    fps: 15,
                    qrbox: { width: 280, height: 180 },
                    aspectRatio: 1.0,
                    formatsToSupport: [
                        window.Html5QrcodeSupportedFormats.EAN_13,
                        window.Html5QrcodeSupportedFormats.EAN_8,
                        window.Html5QrcodeSupportedFormats.CODE_128,
                        window.Html5QrcodeSupportedFormats.CODE_39,
                        window.Html5QrcodeSupportedFormats.UPC_A,
                        window.Html5QrcodeSupportedFormats.UPC_E,
                        window.Html5QrcodeSupportedFormats.QR_CODE,
                    ]
                };

                // Smart camera selection (prefer rear camera on mobile, fallback safely)
                let cameraSource = { facingMode: 'environment' };
                try {
                    const devices = await window.Html5Qrcode.getCameras();
                    if (devices && devices.length > 0) {
                        const rearCamera = devices.find(d => {
                            const label = (d.label || '').toLowerCase();
                            return label.includes('back') || label.includes('rear') || label.includes('environment');
                        });
                        cameraSource = rearCamera ? rearCamera.id : devices[devices.length - 1].id;
                    }
                } catch(e) {
                    console.log('Mobile camera list skipped:', e);
                }

                try {
                    await this.html5QrCode.start(
                        cameraSource,
                        config,
                        (decodedText) => this.onDetected(decodedText),
                        () => {}
                    );
                } catch(primaryErr) {
                    console.warn('Primary camera start failed, fallback to facingMode: environment', primaryErr);
                    await this.html5QrCode.start(
                        { facingMode: 'environment' },
                        config,
                        (decodedText) => this.onDetected(decodedText),
                        () => {}
                    );
                }

                this.isInitializing = false;

                // Torch & Zoom check
                try {
                    const capabilities = this.html5QrCode.getRunningTrackCapabilities();
                    this.hasTorch = Boolean(capabilities && capabilities.torch);
                    this.hasZoom = Boolean(capabilities && capabilities.zoom);
                } catch (e) {
                    this.hasTorch = false;
                    this.hasZoom = false;
                }

            } catch (err) {
                console.error(err);
                this.isInitializing = false;
                this.errorMessage = err.message || 'Tidak dapat mengakses kamera HP';
            }
        },

        async setZoom(level) {
            this.zoomLevel = level;
            if (this.html5QrCode && this.hasZoom) {
                try {
                    await this.html5QrCode.applyVideoConstraints({
                        advanced: [{ zoom: level }]
                    });
                } catch (e) {
                    console.log('Hardware zoom error:', e);
                }
            }

            // Always apply digital CSS magnification as fallback / visual scaling
            const video = document.querySelector('#remote-barcode-reader video');
            if (video) {
                video.style.transition = 'transform 0.2s ease-out';
                video.style.transform = `scale(${level})`;
                video.style.transformOrigin = 'center center';
            }
        },

        async toggleTorch() {
            if (!this.html5QrCode || !this.hasTorch) return;
            try {
                this.torchOn = !this.torchOn;
                await this.html5QrCode.applyVideoConstraints({
                    advanced: [{ torch: this.torchOn }]
                });
            } catch (err) {
                this.torchOn = false;
            }
        },

        onDetected(decodedText) {
            const code = (decodedText || '').trim();
            if (!code || this.cooldown) return;

            this.cooldown = true;

            // Audio & Haptic feedback
            if (!this.isMuted && window.playScanBeep) window.playScanBeep();
            if (window.triggerHaptic) window.triggerHaptic(120);

            // Flash toast on phone
            this.flashMessage = `Terkirim: ${code}`;
            setTimeout(() => { this.flashMessage = ''; }, 1800);

            // Send to server Livewire
            @this.processBarcode(code);

            // 1.5s cooldown before next scan
            setTimeout(() => {
                this.cooldown = false;
            }, 1500);
        },

        submitManual() {
            const code = (this.manualInput || '').trim();
            if (!code) return;

            if (!this.isMuted && window.playScanBeep) window.playScanBeep();
            if (window.triggerHaptic) window.triggerHaptic(100);

            this.flashMessage = `Terkirim: ${code}`;
            setTimeout(() => { this.flashMessage = ''; }, 1800);

            @this.processBarcode(code);
            this.manualInput = '';
        }
    };
}
</script>
