@props([
    'open' => false,
    'token' => '',
    'connected' => false,
    'deviceName' => null,
])

@php
    $lanIp = trim((string) @shell_exec("ip route get 1 2>/dev/null | awk '{print $7; exit}'")) ?: request()->server('SERVER_ADDR', '127.0.0.1');
@endphp

<div
    x-data="{
        currentToken: @js($token),
        connected: @js($connected),
        lanIp: @js($lanIp),
        qrSvg: '',
        copied: false,
        getPairingUrl(val) {
            const t = val || this.currentToken;
            if (!t) return '';
            let origin = window.location.origin;
            if ((window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') && this.lanIp && this.lanIp !== '127.0.0.1') {
                const port = window.location.port ? `:${window.location.port}` : '';
                origin = `${window.location.protocol}//${this.lanIp}${port}`;
            }
            return `${origin}/scanner/${t}`;
        },
        renderQr(val) {
            const t = val || this.currentToken;
            if (!t || !window.QRCode) return;
            const pairingUrl = this.getPairingUrl(t);
            window.QRCode.toString(pairingUrl, {
                type: 'svg',
                width: 210,
                margin: 1,
                color: { dark: '#0f172a', light: '#ffffff' }
            }, (error, svgString) => {
                if (!error && svgString) {
                    this.qrSvg = svgString;
                } else if (error) {
                    console.error('QR render error:', error);
                }
            });
        },
        copyUrl() {
            const pairingUrl = this.getPairingUrl();
            navigator.clipboard.writeText(pairingUrl).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        }
    }"
    x-init="
        renderQr();
        $watch('currentToken', (val) => renderQr(val));
    "
    x-effect="
        if (currentToken !== @js($token)) {
            currentToken = @js($token);
            renderQr(currentToken);
        }
    "
>
    <x-ui.modal
        :open="$open"
        id="scanner-pairing-modal"
        title="Hubungkan Kamera HP (Wireless Scanner)"
        description="Gunakan smartphone Anda sebagai pemindai barcode nirkabel tanpa perlu kabel atau aplikasi tambahan."
        maxWidth="md"
        closeAction="closePairingModal"
        wire:key="scanner-pairing-modal-{{ $token }}"
    >
        <div class="space-y-4 text-center">
            {{-- View 1: Menunggu Koneksi HP (Tampilkan QR Code) --}}
            <div x-show="!connected" class="space-y-3.5">
                <div wire:ignore class="inline-flex p-3 bg-white border border-slate-200 rounded-2xl shadow-sm mx-auto min-w-[220px] min-h-[220px] items-center justify-center">
                    <div x-html="qrSvg" class="rounded-xl overflow-hidden flex items-center justify-center">
                        <div x-show="!qrSvg" class="text-xs text-slate-400 py-10">Membuat QR Code...</div>
                    </div>
                </div>

                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        Menunggu HP memindai QR Code...
                    </span>
                    <p class="text-[11px] text-slate-500 mt-1.5">
                        Arahkan kamera smartphone ke QR Code di atas untuk membuka pemindai.
                    </p>
                </div>

                {{-- Direct Link Fallback / Copy Link --}}
                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <div class="flex items-center justify-between gap-2 bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/60 text-left">
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">URL Pairing Mobile</span>
                            <span class="font-mono text-xs font-semibold text-slate-800 tracking-tight truncate block" x-text="getPairingUrl()"></span>
                        </div>

                        <button
                            type="button"
                            @click="copyUrl()"
                            class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition shrink-0 inline-flex items-center gap-1 shadow-2xs"
                        >
                            <x-heroicon-o-clipboard-document class="w-3.5 h-3.5 text-slate-500" />
                            <span x-text="copied ? 'Tersalin!' : 'Salin Link'"></span>
                        </button>
                    </div>

                    {{-- Local WiFi HTTP Camera Hint --}}
                    <div x-show="window.location.protocol === 'http:'" class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-2.5 text-left text-[11px] text-amber-800 space-y-1">
                        <div class="flex items-center gap-1.5 font-semibold text-amber-900">
                            <x-heroicon-s-information-circle class="w-4 h-4 text-amber-600 shrink-0" />
                            <span>Info Akses Kamera via WiFi Lokal:</span>
                        </div>
                        <p class="leading-relaxed text-amber-700">
                            Karena menggunakan WiFi lokal (HTTP), Chrome di HP membutuhkan izin kamera. Di Chrome HP buka:
                            <code class="bg-white/90 px-1 py-0.5 rounded text-[10px] font-mono text-amber-900 select-all font-semibold">chrome://flags/#unsafely-treat-insecure-origin-as-secure</code>, tambahkan IP laptop (<span class="font-mono font-bold" x-text="lanIp ? `${window.location.protocol}//${lanIp}:${window.location.port || '8000'}` : ''"></span>) lalu aktifkan (Enabled).
                        </p>
                    </div>
                </div>
            </div>

            {{-- View 2: HP Berhasil Terhubung --}}
            <div x-show="connected" class="py-4 space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
                    <x-heroicon-o-check-badge class="w-10 h-10" />
                </div>

                <div>
                    <h4 class="text-sm font-bold text-slate-900">HP Anda Telah Terhubung!</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        Kamera HP kini aktif sebagai remote scanner. Setiap barang yang Anda scan di gudang akan otomatis masuk ke formulir ini.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Status: Nirkabel Siap Memindai</span>
                </div>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between w-full">
                <x-ui.button
                    type="button"
                    variant="danger"
                    size="sm"
                    wire:click="disconnectPairing"
                >
                    Putuskan Sambungan
                </x-ui.button>

                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    wire:click="closePairingModal"
                >
                    <span x-text="connected ? 'Lanjut di Form (HP Tetap Terhubung)' : 'Tutup'"></span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>
