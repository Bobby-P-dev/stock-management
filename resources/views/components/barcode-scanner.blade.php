@props([
    'onScan' => 'handleBarcodeScan',
    'buttonLabel' => 'Kamera',
    'buttonVariant' => 'outline',
    'buttonSize' => 'sm',
])

<div
    wire:ignore
    x-data="barcodeScannerComponent({
        onScanMethod: '{{ $onScan }}'
    })"
    class="inline-block {{ $attributes->get('class') }}"
>
    {{-- Trigger Button --}}
    <x-ui.button
        type="button"
        :variant="$buttonVariant"
        :size="$buttonSize"
        @click="openScanner()"
        title="Buka Scanner Barcode Kamera"
        class="w-full sm:w-auto border-blue-200 text-blue-700 hover:bg-blue-50/80 hover:border-blue-300"
    >
        <x-slot:icon>
            <x-heroicon-o-qr-code class="w-4 h-4 text-blue-600 shrink-0" />
        </x-slot:icon>
        <span>{{ $buttonLabel }}</span>
    </x-ui.button>

    {{-- Scanner Modal Dialog --}}
    <template x-teleport="body">
        <div
            x-show="isOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="scanner-modal-title"
            role="dialog"
            aria-modal="true"
        >
            {{-- Backdrop --}}
            <div
                x-show="isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/80 backdrop-blur-xs transition-opacity"
                @click="closeScanner()"
            ></div>

            {{-- Dialog Container --}}
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
                <div
                    x-show="isOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-md border border-slate-200 p-5"
                    @click.stop
                >
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <x-heroicon-o-qr-code class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 id="scanner-modal-title" class="text-sm font-bold text-slate-900">
                                    Pindai Barcode Kamera
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    Arahkan kamera ke barcode kemasan barang
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="closeScanner()"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition focus:outline-none"
                            aria-label="Tutup modal"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Camera Viewport Container --}}
                    <div class="my-3.5 relative" wire:ignore>
                        <div
                            id="html5-barcode-reader"
                            class="w-full aspect-[4/3] rounded-xl bg-slate-950 overflow-hidden relative flex items-center justify-center border border-slate-800 text-white"
                        ></div>

                        {{-- Loading Spinner Overlay --}}
                        <div x-show="isInitializing" class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center gap-2 p-4 text-center rounded-xl z-10">
                            <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs text-slate-400">Menghubungkan ke kamera...</span>
                        </div>

                        {{-- Laser scanline animation overlay --}}
                        <div
                            x-show="isScanning && !isInitializing"
                            class="absolute inset-x-8 top-1/2 -translate-y-1/2 pointer-events-none flex flex-col items-center"
                        >
                            <div class="w-full h-44 border-2 border-dashed border-blue-400/70 rounded-xl relative overflow-hidden flex items-center justify-center">
                                <div class="absolute w-full h-0.5 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.9)] animate-pulse"></div>
                                <span class="text-[10px] text-blue-200/80 bg-slate-900/60 px-2 py-0.5 rounded backdrop-blur-xs font-medium">
                                    Posisikan Barcode di Kotak Ini
                                </span>
                            </div>
                        </div>

                        {{-- Camera Controls Overlay (Torch & Camera Switch) --}}
                        <div x-show="isScanning && !isInitializing" class="flex items-center justify-between mt-2.5 px-1">
                            {{-- Status notification --}}
                            <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span x-text="statusMessage" class="text-[11px] font-medium text-slate-600 truncate max-w-[200px]"></span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                {{-- Zoom Controls (1x, 2x, 3x) --}}
                                <div class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 p-0.5 text-[11px] font-semibold text-slate-600">
                                    <button
                                        type="button"
                                        @click="setZoom(1)"
                                        class="px-1.5 py-0.5 rounded transition"
                                        :class="zoomLevel === 1 ? 'bg-white shadow-2xs text-blue-600 font-bold' : 'hover:text-slate-900'"
                                        title="Zoom 1x"
                                    >1x</button>
                                    <button
                                        type="button"
                                        @click="setZoom(2)"
                                        class="px-1.5 py-0.5 rounded transition"
                                        :class="zoomLevel === 2 ? 'bg-white shadow-2xs text-blue-600 font-bold' : 'hover:text-slate-900'"
                                        title="Zoom 2x"
                                    >2x</button>
                                    <button
                                        type="button"
                                        @click="setZoom(3)"
                                        class="px-1.5 py-0.5 rounded transition"
                                        :class="zoomLevel === 3 ? 'bg-white shadow-2xs text-blue-600 font-bold' : 'hover:text-slate-900'"
                                        title="Zoom 3x"
                                    >3x</button>
                                </div>

                                {{-- Sound Mute Button --}}
                                <button
                                    type="button"
                                    @click="isMuted = !isMuted"
                                    class="inline-flex items-center justify-center p-1 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition border border-slate-200"
                                    :title="isMuted ? 'Suara Senyap' : 'Suara Aktif'"
                                >
                                    <x-heroicon-o-speaker-wave x-show="!isMuted" class="w-3.5 h-3.5" />
                                    <x-heroicon-o-speaker-x-mark x-show="isMuted" class="w-3.5 h-3.5 text-slate-400" />
                                </button>

                                {{-- Torch/Flashlight Button --}}
                                <button
                                    type="button"
                                    x-show="hasTorch"
                                    @click="toggleTorch()"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium border border-slate-200 text-slate-700 hover:bg-slate-50 transition"
                                    :class="torchOn ? 'bg-amber-50 border-amber-300 text-amber-700' : ''"
                                >
                                    <x-heroicon-o-bolt class="w-3.5 h-3.5" />
                                    <span x-text="torchOn ? 'Senter: ON' : 'Senter'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Camera Error State --}}
                        <div x-show="errorMessage" class="mt-2.5 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs text-left">
                            <div class="flex items-start gap-1.5">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                                <div>
                                    <span class="font-semibold block" x-text="errorMessage"></span>
                                    <span class="text-[11px] text-rose-600 mt-0.5 block">
                                        Pastikan izin kamera telah diaktifkan di browser Anda dan situs diakses via HTTPS / localhost.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Manual Input Fallback --}}
                    <div class="pt-2 border-t border-slate-100">
                        <div @keydown.enter.prevent="submitManualBarcode()" class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    x-model="manualBarcode"
                                    placeholder="Ketik barcode manual..."
                                    class="w-full text-xs font-mono rounded-lg border border-slate-200 px-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                />
                            </div>
                            <x-ui.button type="button" @click="submitManualBarcode()" variant="primary" size="sm" class="shrink-0 text-xs">
                                Masukkan
                            </x-ui.button>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">
                            Didukung oleh HTML5 Camera Scanner
                        </span>
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="closeScanner()"
                        >
                            Selesai
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function barcodeScannerComponent({ onScanMethod }) {
    return {
        isOpen: false,
        isInitializing: false,
        isScanning: false,
        html5QrCode: null,
        statusMessage: 'Kamera aktif, siap memindai...',
        errorMessage: '',
        manualBarcode: '',
        hasTorch: false,
        torchOn: false,
        isMuted: false,
        zoomLevel: 1,
        hasZoom: false,
        lastScannedCode: null,
        cooldownTimer: null,

        openScanner() {
            this.isOpen = true;
            this.errorMessage = '';
            this.statusMessage = 'Menghubungkan kamera...';
            this.isInitializing = true;
            this.manualBarcode = '';
            this.torchOn = false;

            this.$nextTick(() => {
                this.startCamera();
            });
        },

        async startCamera() {
            try {
                if (!window.Html5Qrcode) {
                    throw new Error('Library Html5Qrcode belum siap dimuat.');
                }

                if (this.html5QrCode) {
                    await this.stopCamera();
                }

                const readerElement = document.getElementById('html5-barcode-reader');
                if (!readerElement) return;

                this.html5QrCode = new window.Html5Qrcode('html5-barcode-reader');

                const config = {
                    fps: 15,
                    qrbox: { width: 260, height: 160 },
                    aspectRatio: 1.333333,
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

                // Device detection: prioritize back camera, fallback cleanly to webcam/user camera
                let cameraSource = { facingMode: 'environment' };
                try {
                    const devices = await window.Html5Qrcode.getCameras();
                    if (devices && devices.length > 0) {
                        const backCamera = devices.find(d => {
                            const label = (d.label || '').toLowerCase();
                            return label.includes('back') || label.includes('rear') || label.includes('environment');
                        });
                        cameraSource = backCamera ? backCamera.id : devices[0].id;
                    }
                } catch (e) {
                    console.log('Camera list skipped:', e);
                }

                try {
                    await this.html5QrCode.start(
                        cameraSource,
                        config,
                        (decodedText) => this.onBarcodeDetected(decodedText),
                        () => {}
                    );
                } catch (errPrimary) {
                    console.warn('Primary camera source failed, trying facingMode: user...', errPrimary);
                    await this.html5QrCode.start(
                        { facingMode: 'user' },
                        config,
                        (decodedText) => this.onBarcodeDetected(decodedText),
                        () => {}
                    );
                }

                this.isInitializing = false;
                this.isScanning = true;
                this.statusMessage = 'Siap memindai barcode...';

                // Check torch & zoom capabilities
                try {
                    const capabilities = this.html5QrCode.getRunningTrackCapabilities();
                    this.hasTorch = Boolean(capabilities && capabilities.torch);
                    this.hasZoom = Boolean(capabilities && capabilities.zoom);
                } catch (e) {
                    this.hasTorch = false;
                    this.hasZoom = false;
                }

            } catch (err) {
                console.error('Camera init error:', err);
                this.isInitializing = false;
                this.isScanning = false;
                this.errorMessage = 'Tidak dapat mengakses kamera: ' + (err.message || 'Periksa izin kamera');
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
            const video = document.querySelector('#html5-barcode-reader video');
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
                console.warn('Torch toggle error:', err);
                this.torchOn = false;
            }
        },

        onBarcodeDetected(decodedText) {
            const cleanCode = (decodedText || '').trim();
            if (!cleanCode) return;

            // Debounce / Cooldown protection: avoid scanning the exact same barcode 10 times in 1 second
            if (this.cooldownTimer) {
                return;
            }

            // Provide immediate feedback
            if (!this.isMuted && window.playScanBeep) window.playScanBeep();
            if (window.triggerHaptic) window.triggerHaptic(120);

            this.statusMessage = `Terdeteksi: ${cleanCode}`;

            // Dispatch to Livewire component
            if (this.$wire && typeof this.$wire[onScanMethod] === 'function') {
                this.$wire[onScanMethod](cleanCode);
            }

            // Set cooldown of 1.5s so user can scan again without closing modal
            this.cooldownTimer = setTimeout(() => {
                this.cooldownTimer = null;
                this.statusMessage = 'Siap memindai berikutnya...';
            }, 1500);
        },

        submitManualBarcode() {
            const code = (this.manualBarcode || '').trim();
            if (!code) return;

            if (!this.isMuted && window.playScanBeep) window.playScanBeep();
            if (window.triggerHaptic) window.triggerHaptic(100);

            if (this.$wire && typeof this.$wire[onScanMethod] === 'function') {
                this.$wire[onScanMethod](code);
            }

            this.manualBarcode = '';
            this.statusMessage = `Diinput manual: ${code}`;
        },

        async stopCamera() {
            if (this.html5QrCode) {
                try {
                    if (this.html5QrCode.isScanning) {
                        await this.html5QrCode.stop();
                    }
                    this.html5QrCode.clear();
                } catch (e) {
                    console.warn('Error stopping scanner:', e);
                } finally {
                    this.html5QrCode = null;
                }
            }
            this.isScanning = false;
            this.isInitializing = false;
            this.hasTorch = false;
            this.torchOn = false;
            this.zoomLevel = 1;
            if (this.cooldownTimer) {
                clearTimeout(this.cooldownTimer);
                this.cooldownTimer = null;
            }
        },

        closeScanner() {
            this.stopCamera();
            this.isOpen = false;
        }
    };
}
</script>
