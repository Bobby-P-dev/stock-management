<div class="space-y-6 min-w-0">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5 stroke-[2]" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Barang Masuk</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Stock In
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Catat penerimaan barang dari mitra supplier ke gudang inventaris secara akurat.</p>
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
        <!-- Section 1: Informasi Transaksi -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shrink-0">
                    <x-heroicon-o-document-text class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Transaksi</h2>
                    <p class="text-[11px] text-slate-400">Data tanggal penerimaan fisik, supplier pengirim, dan nomor referensi surat jalan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-3">
                    <x-ui.input
                        id="transaction_date"
                        wire:model="transaction_date"
                        label="Tanggal Transaksi Fisik"
                        type="date"
                        :required="true"
                    />
                </div>

                <div class="lg:col-span-4">
                    <x-searchable-select
                        wire:model="supplier_id"
                        label="Mitra Supplier"
                        :required="true"
                        :options="$suppliers"
                        placeholder="Pilih Supplier Pengirim"
                        searchPlaceholder="Cari nama atau kode supplier..."
                    />
                </div>

                <div class="sm:col-span-2 lg:col-span-5">
                    <x-ui.input
                        id="transaction_note"
                        wire:model="note"
                        label="Catatan Tambahan / Nomor Surat Jalan"
                        type="text"
                        placeholder="Contoh: No PO-2026/09/88 dari supplier, kondisi barang tersegel rapi"
                    />
                </div>
            </div>
        </div>

        <!-- Section 2: Rincian Barang yang Diterima -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-600 shrink-0">
                        <x-heroicon-o-list-bullet class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Barang yang Diterima</h2>
                        <p class="text-[11px] text-slate-400">Kuantitas ini akan otomatis menambah saldo stok aktual di master barang.</p>
                    </div>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200/80 transition shadow-2xs cursor-pointer self-start sm:self-auto"
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
                            <th class="py-3 px-3 font-semibold min-w-[260px]">Barang Diterima <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold w-40 sm:w-48">Kuantitas Masuk <span class="text-rose-500 normal-case">*</span></th>
                            <th class="py-3 px-3 font-semibold w-48 sm:w-56">Harga Satuan (Rp)</th>
                            <th class="py-3 px-3 font-semibold text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $index => $item)
                        <tr
                            wire:key="stock-in-row-{{ $index }}"
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
                                    wire:key="stock-in-select-{{ $index }}"
                                    wire:model="items.{{ $index }}.product_id"
                                    :options="$products"
                                    placeholder="Pilih Barang yang Diterima..."
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
                            </td>

                            <td class="py-3 px-3 align-top">
                                <x-ui.input
                                    wire:model="items.{{ $index }}.unit_price"
                                    type="number"
                                    min="0"
                                    step="500"
                                    placeholder="0"
                                >
                                    <x-slot:icon>
                                        <span class="text-xs font-semibold text-slate-400">Rp</span>
                                    </x-slot:icon>
                                </x-ui.input>
                            </td>

                            <td class="py-3 px-3 text-center align-top pt-3.5">
                                @if(count($items) > 1)
                                <button
                                    @click="window.SwalConfirm({
                                        title: 'Hapus Baris Barang #{{ $index + 1 }}?',
                                        text: 'Baris barang ini akan dihapus dari daftar penerimaan barang masuk.',
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
                variant="success"
                size="md"
                loadingTarget="save"
                @click="window.SwalConfirm({
                    title: 'Simpan Transaksi Barang Masuk?',
                    text: 'Pastikan barang dan kuantitas sudah sesuai surat jalan. Saldo stok barang akan langsung bertambah.',
                    icon: 'question',
                    confirmText: 'Ya, Simpan Transaksi',
                    variant: 'emerald',
                    onConfirm: () => $wire.save()
                })"
            >
                <x-slot:icon>
                    <x-heroicon-o-check class="w-4 h-4 mr-1.5 stroke-[2]" />
                </x-slot:icon>
                Simpan Transaksi Masuk
            </x-ui.button>
        </div>
    </form>
</div>
