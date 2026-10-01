<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Stock In
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Barang Masuk</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Catat penerimaan barang dari mitra supplier ke gudang inventaris.</p>
        </div>

        <a
            href="{{ route('transactions.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
        >
            <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
            Riwayat Mutasi
        </a>
    </div>

    @if($errors->has('items'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-x-circle class="w-4 h-4 text-rose-600 shrink-0" />
        <span>{{ $errors->first('items') }}</span>
    </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 space-y-4 shadow-sm">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-4 h-4 text-indigo-600" />
                Informasi Faktur & Pengirim
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi Fisik *</label>
                    <input
                        wire:model="transaction_date"
                        type="date"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('transaction_date') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mitra Supplier *</label>
                    <select
                        wire:model="supplier_id"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                        <option value="">Pilih Supplier Pengirim</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Nomor Surat Jalan</label>
                <input
                    wire:model="note"
                    type="text"
                    placeholder="Contoh: No PO-2026/09/88 dari supplier, kondisi barang tersegel rapi"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                >
                @error('note') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-o-list-bullet class="w-4 h-4 text-emerald-600" />
                        Rincian Barang yang Diterima
                    </h3>
                    <p class="text-[11px] text-slate-500">Kuantitas ini akan otomatis menambah saldo stok aktual di master barang.</p>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold border border-emerald-200 transition cursor-pointer"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                    Tambah Baris Barang
                </button>
            </div>

            <div class="space-y-3">
                @foreach($items as $index => $item)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex-1 w-full sm:w-auto">
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Barang #{{ $index + 1 }} *</label>
                        <select
                            wire:model="items.{{ $index }}.product_id"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                            <option value="">Pilih Barang...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->sku }} - {{ $product->name }} (Stok saat ini: {{ $product->current_stock }} {{ $product->unit->symbol }})
                            </option>
                            @endforeach
                        </select>
                        @error("items.{$index}.product_id") <span class="text-[10px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full sm:w-32">
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Kuantitas Masuk *</label>
                        <input
                            wire:model="items.{{ $index }}.quantity"
                            type="number"
                            min="1"
                            placeholder="Qty"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 text-center font-bold"
                        >
                        @error("items.{$index}.quantity") <span class="text-[10px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full sm:w-40">
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                        <input
                            wire:model="items.{{ $index }}.unit_price"
                            type="number"
                            min="0"
                            step="500"
                            placeholder="Opsional"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                        @error("items.{$index}.unit_price") <span class="text-[10px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="self-end sm:self-center pt-2 sm:pt-4">
                        @if(count($items) > 1)
                        <button
                            wire:click="removeItem({{ $index }})"
                            type="button"
                            class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                            title="Hapus baris barang ini"
                        >
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a
                href="{{ route('transactions.index') }}"
                class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-300 shadow-sm"
            >
                Batal
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer"
            >
                <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Simpan Transaksi Masuk
                </span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
