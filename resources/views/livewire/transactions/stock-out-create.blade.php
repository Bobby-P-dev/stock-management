<div class="space-y-6 min-w-0">
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
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-2xs">
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
                        :required="true"
                        placeholder="Contoh: Bpk. Ahmad / Pelanggan"
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
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-2xs">
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

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold border border-rose-200/80 transition shadow-2xs cursor-pointer self-start sm:self-auto"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5 stroke-[2.5]" />
                    Tambah Baris Barang
                </button>
            </div>

            <!-- Table of Items (Clean, Aligned, Full Width) -->
            <div class="overflow-visible pb-48">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-600 border-b border-slate-200/80 uppercase tracking-wider text-[11px] bg-slate-50/70">
                            <th class="py-3 px-3 text-center w-12 font-semibold">No</th>
                            <th class="py-3 px-3 font-semibold min-w-[280px]">Barang Keluar <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold w-40 sm:w-56">Kuantitas Keluar <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $index => $item)
                        <tr
                            wire:key="stock-out-row-{{ $index }}"
                            style="z-index: {{ 50 - $index }};"
                            class="hover:bg-slate-50/40 transition relative"
                        >
                            <td class="py-3 px-3 text-center text-slate-500 font-mono align-top pt-4">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold">
                                    {{ $index + 1 }}
                                </span>
                            </td>

                            <td class="py-3 px-3 align-top">
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

                            <td class="py-3 px-3 align-top">
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

                            <td class="py-3 px-3 text-center align-top pt-3.5">
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
                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
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
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-ui.button
                href="{{ route('transactions.index') }}"
                variant="outline"
                size="md"
            >
                Batal
            </x-ui.button>

            <x-ui.button
                type="button"
                variant="danger"
                size="md"
                loadingTarget="save"
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
    </form>
</div>
