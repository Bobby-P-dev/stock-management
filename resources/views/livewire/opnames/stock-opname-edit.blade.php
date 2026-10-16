<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Koreksi Audit
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Berita Acara Stock Opname</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Nomor Dokumen: <span class="font-mono font-bold text-indigo-700">{{ $opname->opname_no }}</span>
            </p>
        </div>

        <a
            href="{{ route('opnames.show', $opname->id) }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition self-start sm:self-auto"
        >
            <x-heroicon-o-arrow-left class="w-4 h-4 text-slate-500" />
            Kembali ke Detail
        </a>
    </div>

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
        <x-heroicon-s-x-circle class="w-5 h-5 text-rose-500 shrink-0" />
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Informasi Header Opname -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-sm">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-indigo-600" />
                Informasi Dokumen Opname
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Pemeriksaan Fisik <span class="text-rose-500">*</span>
                    </label>
                    <input
                        wire:model="opname_date"
                        type="date"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('opname_date') <span class="text-[11px] text-rose-600 mt-0.5 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan Berita Acara</label>
                    <input
                        wire:model="note"
                        type="text"
                        placeholder="Contoh: Koreksi audit fisik berkala..."
                        class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 placeholder:text-slate-400"
                    >
                    @error('note') <span class="text-[11px] text-rose-600 mt-0.5 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Rincian Barang yang Diperiksa -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-o-cube class="w-4 h-4 text-indigo-600" />
                        Daftar Barang yang Diperiksa
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih barang dan masukkan jumlah fisik nyata di rak. Selisih dihitung otomatis.</p>
                </div>

                <button
                    wire:click="addItem"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition cursor-pointer self-start sm:self-auto"
                >
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Tambah Baris Barang
                </button>
            </div>

            @error('items')
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                {{ $message }}
            </div>
            @enderror

            <div class="space-y-3.5">
                @foreach($items as $index => $item)
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200 space-y-3.5">
                    <!-- Baris Atas: Nomor, Dropdown Produk, & Tombol Hapus -->
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0 mt-6">
                            {{ $index + 1 }}
                        </span>

                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Pilih Produk Barang <span class="text-rose-500">*</span>
                            </label>
                            <x-searchable-select
                                wire:key="stock-opname-edit-item-{{ $index }}"
                                wire:model.live="items.{{ $index }}.product_id"
                                :options="$products"
                                placeholder="-- Pilih Produk Barang --"
                                searchPlaceholder="Cari nama barang atau kode SKU..."
                            />
                            @error("items.{$index}.product_id")
                                <span class="text-[10px] text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        @if(count($items) > 1)
                        <button
                            wire:click="removeItem({{ $index }})"
                            type="button"
                            class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer shrink-0 mt-5"
                            title="Hapus baris barang ini"
                        >
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                        @endif
                    </div>

                    <!-- Baris Tengah: Komparasi Stok (Stok Sistem, Stok Fisik, Selisih) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-lg bg-white border border-slate-200/80 items-center">
                        <!-- 1. Stok Sistem (Readonly) -->
                        <div class="flex items-center justify-between sm:justify-start sm:gap-3 px-2">
                            <span class="text-[11px] font-medium text-slate-500">Stok Sistem:</span>
                            <span class="font-mono font-bold text-sm text-slate-800">
                                {{ $item['system_stock'] }}
                            </span>
                        </div>

                        <!-- 2. Stok Fisik (Input) -->
                        <div class="flex items-center justify-between sm:justify-start sm:gap-3 px-2 border-t sm:border-t-0 sm:border-l sm:border-r border-slate-100 pt-2 sm:pt-0">
                            <label class="text-[11px] font-semibold text-slate-700 shrink-0">Stok Fisik Real:</label>
                            <input
                                wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                                type="number"
                                min="0"
                                required
                                class="w-20 px-2.5 py-1 text-center rounded-lg bg-slate-50 border border-slate-300 font-mono font-bold text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                            >
                            @error("items.{$index}.physical_stock")
                                <span class="text-[10px] text-rose-600 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 3. Selisih (Badge) -->
                        <div class="flex items-center justify-between sm:justify-center gap-2 px-2 border-t sm:border-t-0 border-slate-100 pt-2 sm:pt-0">
                            <span class="text-[11px] font-medium text-slate-500 sm:hidden">Hasil Selisih:</span>
                            @php $diff = $item['difference'] ?? 0; @endphp
                            @if($diff === 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                0 (Sesuai)
                            </span>
                            @elseif($diff < 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                {{ $diff }} (Kurang)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                +{{ $diff }} (Lebih)
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Baris Bawah: Alasan Penyesuaian & Catatan Khusus -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-0.5">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Alasan Penyesuaian</label>
                            <x-searchable-select
                                wire:key="stock-opname-edit-reason-{{ $index }}"
                                wire:model="items.{{ $index }}.reason"
                                :options="[
                                    ['value' => 'SESUAI', 'label' => 'Stok Sesuai (Fisik Cocok)'],
                                    ['value' => 'RUSAK', 'label' => 'Barang Rusak / Cacat'],
                                    ['value' => 'HILANG', 'label' => 'Barang Hilang / Kurang'],
                                    ['value' => 'KADALUWARSA', 'label' => 'Kadaluwarsa / Expired'],
                                    ['value' => 'SELISIH_HITUNG', 'label' => 'Koreksi Salah Hitung'],
                                    ['value' => 'LAINNYA', 'label' => 'Alasan Lainnya']
                                ]"
                                placeholder="Pilih Alasan"
                                searchPlaceholder="Cari alasan..."
                            />
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-1">Catatan Khusus (Opsional)</label>
                            <input
                                wire:model="items.{{ $index }}.item_notes"
                                type="text"
                                placeholder="Contoh: Barang tercecer di rak, cacat pabrik..."
                                class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition placeholder:text-slate-400"
                            >
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
                href="{{ route('opnames.show', $opname->id) }}"
                class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
            >
                Batal
            </a>

            <button
                type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition cursor-pointer"
            >
                <x-heroicon-o-check class="w-4 h-4" />
                Perbarui & Sesuaikan Stok Sistem
            </button>
        </div>
    </form>
</div>
