<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Katalog Master Barang</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data inventaris produk, SKU, kategori, dan saldo stok berjalan.</p>
        </div>

        @if(auth()->user()->isAdmin())
        <button
            wire:click="create"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer self-start sm:self-auto"
        >
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Barang Baru
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm">
        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pencarian Barang</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari SKU atau nama barang..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                >
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter Kategori</label>
            <select
                wire:model.live="selectedCategory"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Ketersediaan</label>
            <select
                wire:model.live="stockStatusFilter"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
                <option value="">Semua Status</option>
                <option value="safe">Stok Aman</option>
                <option value="low">Stok Menipis (&le; Min)</option>
                <option value="out">Stok Habis (0)</option>
            </select>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 bg-slate-50/70 border-b border-slate-100">
                        <th class="py-3 px-4 font-semibold">SKU</th>
                        <th class="py-3 px-4 font-semibold">Nama Barang</th>
                        <th class="py-3 px-4 font-semibold">Kategori</th>
                        <th class="py-3 px-4 font-semibold text-center">Stok Terkini</th>
                        <th class="py-3 px-4 font-semibold text-center">Batas Min.</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ $product->sku }}</td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            <div>{{ $product->name }}</div>
                            @if(! $product->is_active)
                            <span class="text-[10px] text-slate-400 font-normal">(Non-aktif)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $product->category->name }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="text-sm font-extrabold text-slate-900">{{ $product->current_stock }}</span>
                            <span class="text-[10px] text-slate-500 font-normal">{{ $product->unit->symbol }}</span>
                        </td>
                        <td class="py-3 px-4 text-center text-slate-500">
                            {{ $product->minimum_stock }} {{ $product->unit->symbol }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($product->isOutOfStock())
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Habis
                            </span>
                            @elseif($product->isLowStock())
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Menipis
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aman
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <a
                                href="{{ route('stock-card', ['productId' => $product->id]) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 font-semibold text-[11px] transition"
                                title="Lihat Kartu Stok"
                            >
                                <x-heroicon-o-table-cells class="w-3.5 h-3.5 text-amber-600" />
                                Kartu Stok
                            </a>

                            @if(auth()->user()->isAdmin())
                            <button
                                wire:click="edit({{ $product->id }})"
                                type="button"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 font-semibold text-[11px] transition cursor-pointer"
                            >
                                <x-heroicon-o-pencil-square class="w-3.5 h-3.5 text-indigo-600" />
                                Edit
                            </button>

                            <button
                                wire:click="toggleActive({{ $product->id }})"
                                type="button"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-slate-600 bg-slate-100 hover:bg-slate-200 font-medium text-[11px] transition cursor-pointer"
                                title="Ubah Status Aktif"
                            >
                                {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Tidak ada barang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">
                    {{ $editingProductId ? 'Edit Data Barang' : 'Tambah Barang Baru' }}
                </h3>
                <button wire:click="$set('showModal', false)" type="button" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode SKU *</label>
                        <input
                            wire:model="sku"
                            type="text"
                            placeholder="Contoh: PRD-001"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                        @error('sku') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Stok Minimum *</label>
                        <input
                            wire:model="minimum_stock"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                        @error('minimum_stock') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Barang *</label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="Contoh: Kabel UTP Cat6 305M"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('name') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                        <select
                            wire:model="category_id"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan *</label>
                        <select
                            wire:model="unit_id"
                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                        >
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                            @endforeach
                        </select>
                        @error('unit_id') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input wire:model="is_active" type="checkbox" id="modal_is_active" class="rounded border-slate-300 text-indigo-600">
                    <label for="modal_is_active" class="text-xs text-slate-700 cursor-pointer">Status Barang Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm"
                    >
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
