<div class="space-y-6 min-w-0" @if($pairingToken !== '') wire:poll.1000ms="checkRemoteScanner" @endif>
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200/60 flex items-center justify-center text-rose-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5 stroke-[2]" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Barang Keluar</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Stock Out
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Catat pengeluaran barang dari gudang untuk pembeli atau penerima barang.</p>
            </div>
        </div>

        <x-ui.button
            href="{{ route('transactions.index') }}"
            variant="outline"
            size="sm"
            class="self-start sm:self-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5 text-slate-500 mr-1.5" />
            </x-slot:icon>
            Riwayat Mutasi
        </x-ui.button>
    </div>

    <!-- Alert Duplikat / Item Validation Error -->
    @if($errors->has('items'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <x-heroicon-s-x-circle class="w-4 h-4 text-rose-600 shrink-0" />
        <span class="font-medium">{{ $errors->first('items') }}</span>
    </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Section 1: Informasi Pengeluaran -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-rose-50 border border-rose-200/60 flex items-center justify-center text-rose-600 shrink-0">
                    <x-heroicon-o-document-text class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Pengeluaran</h2>
                    <p class="text-[11px] text-slate-400">Data tanggal pengeluaran fisik, nama penerima/pembeli, dan keterangan pemakaian barang.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.input
                        id="transaction_date"
                        wire:model="transaction_date"
                        label="Tanggal Pengeluaran Fisik"
                        type="date"
                        :required="true"
                    />
                </div>

                <div>
                    <x-ui.input
                        id="recipient"
                        wire:model="recipient"
                        label="Nama Penerima"
                        type="text"
                        :required="false"
                        placeholder="Contoh: Bpk. Ahmad / Pelanggan (Opsional)"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-ui.input
                        id="transaction_note"
                        wire:model="note"
                        label="Keterangan / Keperluan Pemakaian"
                        type="text"
                        placeholder="Contoh: Pemasangan jaringan baru lantai 2"
                    />
                </div>
            </div>
        </div>

        <!-- Section 2: Rincian Barang yang Dikeluarkan -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-rose-50 border border-rose-200/60 flex items-center justify-center text-rose-600 shrink-0">
                        <x-heroicon-o-list-bullet class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Barang yang Dikeluarkan</h2>
                        <p class="text-[11px] text-slate-400">Sistem memverifikasi ketersediaan stok aktual dan menerapkan proteksi agar saldo fisik tidak minus.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    {{-- Remote Scanner Pairing (HP) --}}
                    @if($pairingConnected)
                        <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold shadow-2xs flex-1 sm:flex-none">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span>HP Terhubung</span>
                            <button
                                type="button"
                                wire:click="openPairing"
                                class="text-[11px] underline text-emerald-800 hover:text-emerald-950 font-medium ml-1"
                            >
                                Info
                            </button>
                        </div>
                    @else
                        <x-ui.button
                            type="button"
                            wire:click="openPairing"
                            variant="secondary"
                            size="sm"
                            title="Jadikan kamera smartphone sebagai barcode scanner nirkabel"
                            class="flex-1 sm:flex-none justify-center border-indigo-200 text-indigo-700 hover:bg-indigo-50/80 hover:border-indigo-300"
                        >
                            <x-slot:icon>
                                <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-indigo-600 shrink-0" />
                            </x-slot:icon>
                            <span>Scan via HP</span>
                        </x-ui.button>
                    @endif

                    {{-- Local Barcode Scanner Button --}}
                    <div class="flex-1 sm:flex-none">
                        <x-barcode-scanner
                            onScan="handleBarcodeScan"
                            buttonLabel="Kamera"
                            buttonVariant="outline"
                            buttonSize="sm"
                            class="w-full"
                        />
                    </div>

                    <button
                        wire:click="addItem"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold border border-rose-200/80 transition shadow-2xs cursor-pointer"
                    >
                        <x-heroicon-o-plus class="w-3.5 h-3.5 stroke-[2.5]" />
                        Tambah Baris Barang
                    </button>
                </div>
            </div>

            <!-- Table / Cards of Items (Clean, Responsive, Full Width) -->
            <div class="overflow-visible pb-32 sm:pb-48">
                <table class="w-full text-left text-xs border-collapse block md:table">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-slate-600 border-b border-slate-200/80 uppercase tracking-wider text-[11px] bg-slate-50/70">
                            <th class="py-3 px-3 text-center w-12 font-semibold">No</th>
                            <th class="py-3 px-3 font-semibold min-w-[280px]">Barang Keluar <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold w-40 sm:w-56">Kuantitas Keluar <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="block md:table-row-group space-y-4 md:space-y-0 md:divide-y md:divide-slate-100">
                        @foreach($items as $index => $item)
                        <tr
                            wire:key="stock-out-row-{{ $index }}"
                            style="z-index: {{ 50 - $index }};"
                            class="relative grid grid-cols-2 gap-3 p-4 bg-slate-50/60 md:bg-transparent border border-slate-200/80 md:border-0 rounded-2xl md:rounded-none md:p-0 md:table-row hover:bg-slate-50/40 transition shadow-2xs md:shadow-none"
                        >
                            {{-- Col 1: Nomor / Card Header on Mobile --}}
                            <td class="col-span-2 md:col-auto md:table-cell p-0 md:py-3 md:px-3 text-slate-500 font-mono align-top md:pt-4">
                                {{-- Mobile Header: "Barang #1" with space on right for remove button --}}
                                <div class="flex items-center justify-between pb-2.5 mb-1 border-b border-slate-200/70 md:hidden pr-9">
                                    <span class="text-xs font-bold text-slate-800 tracking-tight font-sans">
                                        Barang #{{ $index + 1 }}
                                    </span>
                                </div>

                                {{-- Desktop Badge --}}
                                <span class="hidden md:inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold">
                                    {{ $index + 1 }}
                                </span>
                            </td>

                            {{-- Col 2: Barang Keluar (Full Width on Mobile) --}}
                            <td class="col-span-2 md:col-auto md:table-cell p-0 md:py-3 md:px-3 align-top min-w-0">
                                <label class="block md:hidden text-xs font-semibold text-slate-700 mb-1.5 font-sans">
                                    Barang Keluar <span class="text-rose-500">*</span>
                                </label>
                                <x-searchable-select
                                    wire:key="stock-out-select-{{ $index }}"
                                    wire:model="items.{{ $index }}.product_id"
                                    :options="$products"
                                    placeholder="Pilih Barang yang Dikeluarkan..."
                                    searchPlaceholder="Cari nama barang atau SKU..."
                                />
                                @error("items.{$index}.product_id")
                                    <span class="mt-1 text-[11px] font-medium text-rose-600 block">{{ $message }}</span>
                                @enderror
                            </td>

                            {{-- Col 3: Kuantitas Keluar --}}
                            <td class="col-span-2 sm:col-span-1 md:col-auto md:table-cell p-0 md:py-3 md:px-3 align-top min-w-0">
                                <label class="block md:hidden text-xs font-semibold text-slate-700 mb-1.5 font-sans">
                                    Kuantitas Keluar <span class="text-rose-500">*</span>
                                </label>
                                <x-ui.input
                                    wire:model="items.{{ $index }}.quantity"
                                    type="number"
                                    min="1"
                                    required
                                    placeholder="1"
                                />
                                @error("items.{$index}.quantity")
                                    <span class="mt-1 text-[11px] font-medium text-rose-600 block">{{ $message }}</span>
                                @enderror
                            </td>

                            {{-- Col 4: Aksi (Remove) --}}
                            <td class="absolute top-3 right-3 md:static md:col-auto md:table-cell p-0 md:py-3 md:px-3 text-center align-top md:pt-3.5">
                                @if(count($items) > 1)
                                <button
                                    @click="window.SwalConfirm({
                                        title: 'Hapus Baris Barang #{{ $index + 1 }}?',
                                        text: 'Baris barang ini akan dihapus dari daftar pengeluaran barang keluar.',
                                        icon: 'warning',
                                        confirmText: 'Ya, Hapus Baris',
                                        variant: 'rose',
                                        onConfirm: () => $wire.removeItem({{ $index }})
                                    })"
                                    type="button"
                                    class="p-1.5 md:p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                    title="Hapus baris barang ini"
                                >
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3: Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
            <div class="flex items-center gap-2">
                @if($pairingConnected)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold shadow-2xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        HP Siap Scan Berkelanjutan
                    </span>
                @endif
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 w-full sm:w-auto">
                <x-ui.button
                    href="{{ route('transactions.index') }}"
                    variant="outline"
                    size="md"
                    class="w-full sm:w-auto justify-center"
                >
                    Batal
                </x-ui.button>

                <x-ui.button
                    type="button"
                    variant="secondary"
                    size="md"
                    wire:click="saveAndCreateNew"
                    loadingTarget="saveAndCreateNew"
                    title="Simpan pengeluaran ini dan langsung buka formulir baru tanpa memutus kamera HP"
                    class="w-full sm:w-auto justify-center border-rose-300 text-rose-800 hover:bg-rose-50"
                >
                    <x-slot:icon>
                        <x-heroicon-o-plus-circle class="w-4 h-4 text-rose-600 mr-1" />
                    </x-slot:icon>
                    Simpan & Input Baru
                </x-ui.button>

                <x-ui.button
                    type="button"
                    variant="danger"
                    size="md"
                    loadingTarget="save"
                    class="w-full sm:w-auto justify-center"
                    @click="window.SwalConfirm({
                        title: 'Simpan Transaksi Barang Keluar?',
                        text: 'Pastikan penerima dan kuantitas barang keluar sudah benar. Saldo stok akan langsung dikurangi.',
                        icon: 'question',
                        confirmText: 'Ya, Simpan Transaksi',
                        variant: 'rose',
                        onConfirm: () => $wire.save()
                    })"
                >
                    <x-slot:icon>
                        <x-heroicon-o-check class="w-4 h-4 mr-1.5 stroke-[2]" />
                    </x-slot:icon>
                    Simpan Transaksi Keluar
                </x-ui.button>
            </div>
        </div>
    </form>

    {{-- Scanner Pairing Modal (Laptop <-> HP) --}}
    <x-scanner-pairing-modal
        :open="$showPairingModal"
        :token="$pairingToken"
        :connected="$pairingConnected"
        :deviceName="$pairingDeviceName"
    />
</div>
