<div class="max-w-5xl mx-auto space-y-6">
    @if ($errors->has('items'))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-rose-500 shrink-0" />
        <span>{{ $errors->first('items') }}</span>
    </div>
    @endif

    <form wire:submit="save" class="bg-white rounded-3xl p-6 sm:p-8 md:p-10 shadow-sm border border-slate-200/60 space-y-8">
        <!-- Header Dokumen: Judul & Tombol Kembali -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Audit Fisik
                    </span>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Pemeriksaan Stock Opname</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">Hitung stok fisik di rak gudang dan bandingkan dengan saldo sistem secara langsung.</p>
            </div>

            <a
                href="{{ route('opnames.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200 transition self-start sm:self-auto"
            >
                <x-heroicon-o-arrow-left class="w-4 h-4 text-slate-500" />
                Kembali ke Daftar
            </a>
        </div>

        <!-- Section 1: Informasi Pelaksanaan Opname -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-indigo-600" />
                Informasi Dokumen Opname
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Perhitungan Fisik <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="opname_date"
                        type="date"
                        required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    >
                    @error('opname_date') <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Catatan / Berita Acara Singkat
                    </label>
                    <input
                        wire:model="note"
                        type="text"
                        placeholder="Contoh: Audit stok triwulan 3, rak blok A..."
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition placeholder:text-slate-400"
                    >
                    @error('note') <span class="text-[11px] text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Daftar Barang yang Diperiksa -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-o-cube class="w-4 h-4 text-indigo-600" />
                        Daftar Barang yang Diperiksa
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Sesuaikan jumlah fisik nyata di rak dengan saldo sistem.</p>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition cursor-pointer"
                >
                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                    Tambah Barang
                </button>
            </div>

            <!-- Shaded Container: Box Abu-abu Lembut seperti di Referensi -->
            <div class="bg-slate-50/80 rounded-2xl p-4 sm:p-5 border border-slate-200/80 space-y-3">
                <!-- Desktop Table Headers -->
                <div class="hidden lg:grid grid-cols-12 gap-3 px-1 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <div class="col-span-3">Produk Barang <span class="text-rose-500">*</span></div>
                    <div class="col-span-1 text-center">Sistem</div>
                    <div class="col-span-1 text-center">Fisik <span class="text-rose-500">*</span></div>
                    <div class="col-span-2 text-center">Selisih</div>
                    <div class="col-span-2">Alasan</div>
                    <div class="col-span-2">Catatan Khusus</div>
                    <div class="col-span-1 text-center">Aksi</div>
                </div>

                <!-- Desktop Table Rows -->
                <div class="space-y-2.5">
                    @foreach($items as $index => $item)
                    <!-- Desktop View (Horizontal Single-Row Grid) -->
                    <div
                        wire:key="stock-opname-create-row-{{ $index }}"
                        style="z-index: {{ 50 - $index }};"
                        class="relative hidden lg:grid grid-cols-12 gap-3 items-center py-1"
                    >
                        <!-- Produk Barang (Col 3) -->
                        <div class="col-span-3">
                            <x-searchable-select
                                wire:key="stock-opname-create-item-{{ $index }}"
                                wire:model.live="items.{{ $index }}.product_id"
                                :options="$products"
                                placeholder="-- Pilih Produk --"
                                searchPlaceholder="Cari nama atau SKU..."
                            />
                            @error("items.{$index}.product_id")
                                <span class="text-[10px] text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Stok Sistem (Col 1) -->
                        <div class="col-span-1">
                            <div class="h-[38px] px-2 rounded-xl bg-white border border-slate-200/90 font-mono font-bold text-xs text-slate-700 flex items-center justify-center shadow-2xs">
                                {{ $item['system_stock'] }}
                            </div>
                        </div>

                        <!-- Stok Fisik Real (Col 1) -->
                        <div class="col-span-1">
                            <input
                                wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                                type="number"
                                min="0"
                                required
                                class="w-full h-[38px] text-center rounded-xl bg-white border border-slate-300 font-mono font-bold text-xs text-slate-900 focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 shadow-2xs"
                            >
                            @error("items.{$index}.physical_stock")
                                <span class="text-[10px] text-rose-600 block text-center mt-0.5">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Selisih (Col 2) -->
                        <div class="col-span-2">
                            <div class="h-[38px] flex items-center justify-center">
                                @php $diff = $item['difference'] ?? 0; @endphp
                                @if($diff === 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    0 (Sesuai)
                                </span>
                                @elseif($diff < 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ $diff }} (Kurang)
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    +{{ $diff }} (Lebih)
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Alasan Penyesuaian (Col 2) -->
                        <div class="col-span-2">
                            <x-searchable-select
                                wire:key="stock-opname-create-reason-{{ $index }}"
                                wire:model="items.{{ $index }}.reason"
                                :options="[
                                    ['value' => 'SESUAI', 'label' => 'Stok Sesuai'],
                                    ['value' => 'RUSAK', 'label' => 'Barang Rusak'],
                                    ['value' => 'HILANG', 'label' => 'Barang Hilang'],
                                    ['value' => 'KADALUWARSA', 'label' => 'Kadaluwarsa'],
                                    ['value' => 'SELISIH_HITUNG', 'label' => 'Koreksi Hitung'],
                                    ['value' => 'LAINNYA', 'label' => 'Lainnya']
                                ]"
                                placeholder="Pilih Alasan..."
                                searchPlaceholder="Cari alasan..."
                            />
                            @error("items.{$index}.reason")
                                <span class="text-[10px] text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Catatan Khusus (Col 2) -->
                        <div class="col-span-2">
                            <input
                                wire:model="items.{{ $index }}.item_notes"
                                type="text"
                                placeholder="Catatan fisik..."
                                class="w-full h-[38px] px-3 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400 shadow-2xs"
                            >
                        </div>

                        <!-- Aksi (Col 1) -->
                        <div class="col-span-1 flex items-center justify-center">
                            @if(count($items) > 1)
                            <button
                                wire:click="removeItem({{ $index }})"
                                type="button"
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                title="Hapus baris barang ini"
                            >
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                            @else
                            <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </div>
                    </div>

                    <!-- Mobile View (Card untuk layar < 1024px) -->
                    <div
                        wire:key="stock-opname-create-mobile-{{ $index }}"
                        class="lg:hidden p-4 rounded-xl bg-white border border-slate-200/90 space-y-3 shadow-2xs"
                    >
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                Barang #{{ $index + 1 }}
                            </span>

                            @if(count($items) > 1)
                            <button
                                wire:click="removeItem({{ $index }})"
                                type="button"
                                class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 font-semibold p-1"
                            >
                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                Hapus
                            </button>
                            @endif
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pilih Produk Barang *</label>
                            <x-searchable-select
                                wire:key="stock-opname-create-mobile-item-{{ $index }}"
                                wire:model.live="items.{{ $index }}.product_id"
                                :options="$products"
                                placeholder="-- Pilih Produk --"
                                searchPlaceholder="Cari nama atau SKU..."
                            />
                            @error("items.{$index}.product_id") <span class="text-[10px] text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Sistem</label>
                                <div class="h-[38px] rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-mono font-bold text-xs flex items-center justify-center">
                                    {{ $item['system_stock'] }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1 text-center">Fisik Real *</label>
                                <input
                                    wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full h-[38px] text-center rounded-lg bg-white border border-slate-300 font-mono font-bold text-xs text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                >
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Selisih</label>
                                <div class="h-[38px] flex items-center justify-center">
                                    @php $diff = $item['difference'] ?? 0; @endphp
                                    @if($diff === 0)
                                    <span class="w-full h-[38px] inline-flex items-center justify-center px-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        0 (Sesuai)
                                    </span>
                                    @elseif($diff < 0)
                                    <span class="w-full h-[38px] inline-flex items-center justify-center px-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $diff }} (Kurang)
                                    </span>
                                    @else
                                    <span class="w-full h-[38px] inline-flex items-center justify-center px-1 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        +{{ $diff }} (Lebih)
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Alasan Penyesuaian *</label>
                            <x-searchable-select
                                wire:key="stock-opname-create-mobile-reason-{{ $index }}"
                                wire:model="items.{{ $index }}.reason"
                                :options="[
                                    ['value' => 'SESUAI', 'label' => 'Stok Sesuai'],
                                    ['value' => 'RUSAK', 'label' => 'Barang Rusak'],
                                    ['value' => 'HILANG', 'label' => 'Barang Hilang'],
                                    ['value' => 'KADALUWARSA', 'label' => 'Kadaluwarsa'],
                                    ['value' => 'SELISIH_HITUNG', 'label' => 'Koreksi Hitung'],
                                    ['value' => 'LAINNYA', 'label' => 'Lainnya']
                                ]"
                                placeholder="Pilih Alasan..."
                                searchPlaceholder="Cari alasan..."
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Catatan Khusus (Opsional)</label>
                            <input
                                wire:model="items.{{ $index }}.item_notes"
                                type="text"
                                placeholder="Catatan kondisi fisik..."
                                class="w-full h-[38px] px-3 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400"
                            >
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Footer Box: Tambah Baris & Total Item -->
                <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between">
                    <button
                        wire:click="addItem"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                    >
                        <x-heroicon-o-plus-circle class="w-4 h-4" />
                        Tambah Baris Barang Lainnya
                    </button>

                    <p class="text-[11px] text-slate-500 font-medium">Total item diperiksa: <strong class="text-slate-800 font-bold">{{ count($items) }} barang</strong></p>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Bawah (seperti Save & Continue di referensi) -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a
                href="{{ route('opnames.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition"
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

