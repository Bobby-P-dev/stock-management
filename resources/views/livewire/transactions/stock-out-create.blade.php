<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    Stock Out
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Barang Keluar</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Catat pengeluaran barang dari gudang untuk divisi internal atau pemohon.</p>
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
                <x-heroicon-o-document-text class="w-4 h-4 text-rose-600" />
                Informasi Pengeluaran
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengeluaran Fisik *</label>
                    <input
                        wire:model="transaction_date"
                        type="date"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('transaction_date') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penerima / Divisi Pemohon *</label>
                    <input
                        wire:model="recipient"
                        type="text"
                        placeholder="Contoh: Divisi IT Support / Bpk. Ahmad"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('recipient') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Keperluan Pemakaian</label>
                <input
                    wire:model="note"
                    type="text"
                    placeholder="Contoh: Pemasangan jaringan baru lantai 2"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                >
                @error('note') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-o-list-bullet class="w-4 h-4 text-rose-600" />
                        Rincian Barang yang Dikeluarkan
                    </h3>
                    <p class="text-[11px] text-slate-500">Sistem memverifikasi ketersediaan stok aktual dan menerapkan proteksi agar saldo fisik tidak minus.</p>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold border border-rose-200 transition cursor-pointer"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                    Tambah Baris Barang
                </button>
            </div>

            <div class="space-y-3">
                @foreach($items as $index => $item)
                @php
                    $selectedProd = !empty($item['product_id']) ? $products->firstWhere('id', $item['product_id']) : null;
                @endphp
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex-1 w-full sm:w-auto">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[10px] font-semibold text-slate-600">Barang #{{ $index + 1 }} *</label>
                            @if($selectedProd)
                            <span class="text-[10px] {{ $selectedProd->current_stock > 0 ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-bold' }}">
                                Stok Tersedia: {{ $selectedProd->current_stock }} {{ $selectedProd->unit->symbol }}
                            </span>
                            @endif
                        </div>
                        <select
                            wire:model.live="items.{{ $index }}.product_id"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                            <option value="">Pilih Barang...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->sku }} - {{ $product->name }} (Sisa: {{ $product->current_stock }} {{ $product->unit->symbol }})
                            </option>
                            @endforeach
                        </select>
                        @error("items.{$index}.product_id") <span class="text-[10px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="block text-[10px] font-semibold text-slate-600 mb-1">Kuantitas Keluar *</label>
                        <input
                            wire:model="items.{{ $index }}.quantity"
                            type="number"
                            min="1"
                            placeholder="Qty"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 text-center font-bold"
                        >
                        @error("items.{$index}.quantity") <span class="text-[10px] text-rose-600 mt-1 block">{{ $message }}</span> @enderror
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
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer"
            >
                <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Simpan Transaksi Keluar
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
