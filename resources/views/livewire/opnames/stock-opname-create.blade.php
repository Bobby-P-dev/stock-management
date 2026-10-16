<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('opnames.index') }}"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition inline-flex items-center"
                >
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Pemeriksaan Stock Opname</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1 pl-8">Hitung stok fisik di rak gudang dan bandingkan dengan pencatatan sistem secara langsung.</p>
        </div>

        <a
            href="{{ route('opnames.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
        >
            Batal
        </a>
    </div>

    @if ($errors->has('items'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600 shrink-0" />
        <span>{{ $errors->first('items') }}</span>
    </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pelaksanaan Opname</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Perhitungan Fisik *</label>
                    <input
                        wire:model="opname_date"
                        type="date"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                    @error('opname_date')
                        <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan / Berita Acara Singkat</label>
                    <input
                        wire:model="note"
                        type="text"
                        placeholder="Contoh: Audit stok triwulan 3, rak blok A..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                    @error('note')
                        <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Barang yang Diperiksa</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih barang, masukkan jumlah fisik nyata di rak, sistem akan menghitung selisih otomatis.</p>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition cursor-pointer"
                >
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Tambah Baris Barang
                </button>
            </div>

            <div class="space-y-4">
                @foreach($items as $index => $item)
                <div
                    wire:key="opname-create-item-card-{{ $index }}"
                    style="z-index: {{ 40 - $index }};"
                    class="relative p-4 sm:p-5 rounded-xl bg-slate-50/80 border border-slate-200/90 space-y-4 transition"
                >
                    <!-- Baris Header Item: Nomor Urut Barang & Tombol Hapus -->
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-xs font-bold text-slate-800">
                                Pemeriksaan Barang #{{ $index + 1 }}
                            </span>
                        </div>

                        @if(count($items) > 1)
                        <button
                            wire:click="removeItem({{ $index }})"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200/60 transition cursor-pointer"
                            title="Hapus baris barang ini"
                        >
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            <span>Hapus</span>
                        </button>
                        @endif
                    </div>

                    <!-- 2 Kolom Sejajar: Kiri (Barang & Stok) | Kanan (Alasan & Catatan) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Kolom Kiri: Produk & Metrik Stok -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Pilih Produk Barang <span class="text-rose-500">*</span>
                                </label>
                                <x-searchable-select
                                    wire:key="stock-opname-create-item-{{ $index }}"
                                    wire:model.live="items.{{ $index }}.product_id"
                                    :options="$products"
                                    placeholder="-- Pilih Produk Barang --"
                                    searchPlaceholder="Cari nama barang atau kode SKU..."
                                />
                                @error("items.{$index}.product_id")
                                    <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <div class="grid grid-cols-3 gap-2">
                                    <!-- 1. Stok Sistem -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1 text-center">
                                            Stok Sistem
                                        </label>
                                        <div class="h-[38px] px-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-mono font-bold text-sm flex items-center justify-center">
                                            {{ $item['system_stock'] }}
                                        </div>
                                    </div>

                                    <!-- 2. Stok Fisik Real -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1 text-center">
                                            Stok Fisik <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                                            type="number"
                                            min="0"
                                            required
                                            class="w-full h-[38px] px-2 text-center rounded-lg bg-white border border-slate-300 font-mono font-bold text-sm text-slate-900 focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                        >
                                        @error("items.{$index}.physical_stock")
                                            <span class="text-[10px] text-rose-600 block text-center mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- 3. Selisih -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1 text-center">
                                            Selisih
                                        </label>
                                        <div class="h-[38px] flex items-center justify-center">
                                            @php $diff = $item['difference'] ?? 0; @endphp
                                            @if($diff === 0)
                                            <span class="w-full h-[38px] inline-flex items-center justify-center gap-1 px-2 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                0 (Sesuai)
                                            </span>
                                            @elseif($diff < 0)
                                            <span class="w-full h-[38px] inline-flex items-center justify-center gap-1 px-2 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                {{ $diff }} (Kurang)
                                            </span>
                                            @else
                                            <span class="w-full h-[38px] inline-flex items-center justify-center gap-1 px-2 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                +{{ $diff }} (Lebih)
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Alasan & Catatan -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Alasan Penyesuaian <span class="text-rose-500">*</span>
                                </label>
                                <x-searchable-select
                                    wire:key="stock-opname-create-reason-{{ $index }}"
                                    wire:model="items.{{ $index }}.reason"
                                    :options="[
                                        ['value' => 'SESUAI', 'label' => 'Stok Sesuai (Fisik Cocok)'],
                                        ['value' => 'RUSAK', 'label' => 'Barang Rusak / Cacat'],
                                        ['value' => 'HILANG', 'label' => 'Barang Hilang / Kurang'],
                                        ['value' => 'KADALUWARSA', 'label' => 'Kadaluwarsa / Expired'],
                                        ['value' => 'SELISIH_HITUNG', 'label' => 'Koreksi Salah Hitung'],
                                        ['value' => 'LAINNYA', 'label' => 'Alasan Lainnya']
                                    ]"
                                    placeholder="Pilih Alasan..."
                                    searchPlaceholder="Cari alasan..."
                                />
                                @error("items.{$index}.reason")
                                    <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Catatan Khusus (Opsional)
                                </label>
                                <input
                                    wire:model="items.{{ $index }}.item_notes"
                                    type="text"
                                    placeholder="Contoh: Barang tercecer di rak, cacat pabrik..."
                                    class="w-full h-[38px] px-3 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                >
                    <x-heroicon-o-plus-circle class="w-4 h-4" />
                    Tambah Barang Lainnya
                </button>

                <p class="text-[11px] text-slate-400">Total item diperiksa: <strong class="text-slate-700">{{ count($items) }} barang</strong></p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a
                href="{{ route('opnames.index') }}"
                class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
            >
                Batal
            </a>

            <button
                type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition cursor-pointer"
            >
                <x-heroicon-o-check class="w-4 h-4" />
                Simpan & Sesuaikan Stok Sistem
            </button>
        </div>
    </form>
</div>

