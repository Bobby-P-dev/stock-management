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

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 bg-slate-50/80 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3 font-semibold w-10 text-center">No</th>
                            <th class="py-3 px-3 font-semibold min-w-[220px]">Pilih Produk Barang *</th>
                            <th class="py-3 px-3 font-semibold text-center w-28">Stok Sistem</th>
                            <th class="py-3 px-3 font-semibold text-center w-32">Stok Fisik *</th>
                            <th class="py-3 px-3 font-semibold text-center w-28">Selisih</th>
                            <th class="py-3 px-3 font-semibold min-w-[160px]">Alasan Selisih</th>
                            <th class="py-3 px-3 font-semibold min-w-[180px]">Catatan Khusus</th>
                            <th class="py-3 px-3 font-semibold w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $index => $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-3 text-center text-slate-400 font-mono">
                                {{ $index + 1 }}
                            </td>

                            <td class="py-3 px-3">
                                <select
                                    wire:model.live="items.{{ $index }}.product_id"
                                    required
                                    class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition font-medium"
                                >
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.product_id")
                                    <span class="text-[10px] text-rose-600 mt-0.5 block">{{ $message }}</span>
                                @enderror
                            </td>

                            <td class="py-3 px-3 text-center font-bold text-slate-700 bg-slate-50/50">
                                {{ $item['system_stock'] }}
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-24 text-center px-2 py-1.5 rounded-lg bg-white border border-slate-300 font-bold text-slate-900 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                                >
                                @error("items.{$index}.physical_stock")
                                    <span class="text-[10px] text-rose-600 mt-0.5 block">{{ $message }}</span>
                                @enderror
                            </td>

                            <td class="py-3 px-3 text-center">
                                @php $diff = $item['difference'] ?? 0; @endphp
                                @if($diff === 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    0 (Cocok)
                                </span>
                                @elseif($diff < 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $diff }} (Kurang)
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    +{{ $diff }} (Lebih)
                                </span>
                                @endif
                            </td>

                            <td class="py-3 px-3">
                                <select
                                    wire:model="items.{{ $index }}.reason"
                                    class="w-full px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                                >
                                    <option value="SESUAI">Stok Sesuai (Fisik Cocok)</option>
                                    <option value="RUSAK">Barang Rusak / Cacat</option>
                                    <option value="HILANG">Barang Hilang / Kurang</option>
                                    <option value="KADALUWARSA">Kadaluwarsa / Expired</option>
                                    <option value="SELISIH_HITUNG">Koreksi Salah Hitung</option>
                                    <option value="LAINNYA">Alasan Lainnya</option>
                                </select>
                            </td>

                            <td class="py-3 px-3">
                                <input
                                    wire:model="items.{{ $index }}.item_notes"
                                    type="text"
                                    placeholder="Keterangan..."
                                    class="w-full px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                                >
                            </td>

                            <td class="py-3 px-3 text-center">
                                @if(count($items) > 1)
                                <button
                                    wire:click="removeItem({{ $index }})"
                                    type="button"
                                    class="text-slate-400 hover:text-rose-600 transition p-1 cursor-pointer"
                                    title="Hapus baris"
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

