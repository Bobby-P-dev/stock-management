<div class="space-y-5 min-w-0">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Katalog Master Barang</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data inventaris produk, SKU, kategori, dan saldo stok berjalan.</p>
        </div>

        @if(auth()->user()->isAdmin())
            <x-ui.button
                wire:click="create"
                variant="primary"
                size="md"
                class="self-start sm:self-auto"
            >
                <x-slot:icon>
                    <x-heroicon-o-plus class="w-4 h-4" />
                </x-slot:icon>
                <span>Tambah Barang Baru</span>
            </x-ui.button>
        @endif
    </div>

    {{-- Filter Toolbar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
            <div class="md:col-span-5">
                <x-ui.input
                    id="product-search"
                    label="Pencarian Barang"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari SKU, barcode, atau nama barang..."
                    :showClear="$search !== ''"
                    clearAction="$set('search', '')"
                >
                    <x-slot:icon>
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </x-slot:icon>
                </x-ui.input>
            </div>

            <div class="md:col-span-4">
                <x-searchable-select
                    label="Filter Kategori"
                    wire:model.live="selectedCategory"
                    :options="collect([['value' => '', 'label' => 'Semua Kategori']])->concat($categories)"
                    placeholder="Semua Kategori"
                    searchPlaceholder="Cari kategori..."
                />
            </div>

            <div class="md:col-span-3">
                <x-searchable-select
                    label="Status Ketersediaan"
                    wire:model.live="stockStatusFilter"
                    :options="[
                        ['value' => '', 'label' => 'Semua Status'],
                        ['value' => 'safe', 'label' => 'Stok Aman'],
                        ['value' => 'low', 'label' => 'Stok Menipis (<= Min)'],
                        ['value' => 'out', 'label' => 'Stok Habis (0)']
                    ]"
                    placeholder="Semua Status"
                    searchPlaceholder="Cari status stok..."
                />
            </div>
        </div>

        @if($search !== '' || $selectedCategory !== '' || $stockStatusFilter !== '')
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                <span class="text-slate-500">Menampilkan hasil dengan filter aktif</span>
                <x-ui.button
                    variant="ghost"
                    size="xs"
                    wire:click="$set('search', ''); $set('selectedCategory', ''); $set('stockStatusFilter', '')"
                    class="text-blue-600 hover:text-blue-700 hover:bg-blue-50/70"
                >
                    <x-slot:icon>
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    </x-slot:icon>
                    <span>Reset Filter</span>
                </x-ui.button>
            </div>
        @endif
    </div>

    {{-- Inventory Data Table --}}
    <x-ui.table loadingTarget="search,selectedCategory,stockStatusFilter,gotoPage,previousPage,nextPage,delete,toggleActive">
        <x-slot:head>
            <x-ui.th class="whitespace-nowrap">SKU / Barcode</x-ui.th>
            <x-ui.th>Nama Barang</x-ui.th>
            <x-ui.th class="whitespace-nowrap">Kategori</x-ui.th>
            <x-ui.th align="right" class="whitespace-nowrap">Stok Terkini</x-ui.th>
            <x-ui.th align="right" class="whitespace-nowrap">Batas Minimum</x-ui.th>
            <x-ui.th align="center" class="whitespace-nowrap">Status</x-ui.th>
            <x-ui.th align="right" class="whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse($products as $product)
            <x-ui.tr wire:key="product-row-{{ $product->id }}">
                {{-- SKU & Barcode --}}
                <x-ui.td class="whitespace-nowrap">
                    <div class="flex flex-col gap-1 items-start">
                        <x-ui.badge variant="mono" size="code">
                            {{ $product->sku }}
                        </x-ui.badge>
                        @if($product->barcode)
                            <span class="inline-flex items-center gap-1 text-[11px] font-mono text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/70" title="Barcode Pabrik">
                                <x-heroicon-o-qr-code class="w-3 h-3 text-slate-400 shrink-0" />
                                <span>{{ $product->barcode }}</span>
                            </span>
                        @endif
                    </div>
                </x-ui.td>

                {{-- Nama Barang --}}
                <x-ui.td class="min-w-[200px] max-w-[340px]">
                    <div class="flex items-start gap-2">
                        <span class="text-[13px] font-semibold text-slate-900 leading-snug break-words">
                            {{ $product->name }}
                        </span>
                        @if(! $product->is_active)
                            <x-ui.badge variant="neutral" size="xs" class="shrink-0 mt-0.5">
                                Non-aktif
                            </x-ui.badge>
                        @endif
                    </div>
                </x-ui.td>

                {{-- Kategori --}}
                <x-ui.td class="text-slate-600 font-medium whitespace-nowrap">
                    {{ $product->category?->name ?? '-' }}
                </x-ui.td>

                {{-- Stok Terkini --}}
                <x-ui.td align="right" :numeric="true">
                    <span class="text-sm font-bold {{ $product->isOutOfStock() ? 'text-rose-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-slate-900') }}">
                        {{ $product->current_stock }}
                    </span>
                    <span class="text-xs text-slate-500 font-normal ml-0.5">{{ $product->unit?->symbol }}</span>
                </x-ui.td>

                {{-- Batas Minimum --}}
                <x-ui.td align="right" :numeric="true" class="text-slate-600">
                    <span class="font-medium">{{ $product->minimum_stock }}</span>
                    <span class="text-slate-400 ml-0.5">{{ $product->unit?->symbol }}</span>
                </x-ui.td>

                {{-- Status --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    @if($product->isOutOfStock())
                        <x-ui.badge variant="danger">Habis</x-ui.badge>
                    @elseif($product->isLowStock())
                        <x-ui.badge variant="warning">Menipis</x-ui.badge>
                    @else
                        <x-ui.badge variant="success">Aman</x-ui.badge>
                    @endif
                </x-ui.td>

                {{-- Row Actions --}}
                <x-ui.td align="right" class="whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        {{-- Visible Action: Kartu Stok --}}
                        <x-ui.button
                            :href="route('stock-card', ['productId' => $product->id])"
                            variant="outline"
                            size="xs"
                            class="hover:text-blue-600"
                            title="Lihat Kartu Stok"
                        >
                            <x-slot:icon>
                                <x-heroicon-o-table-cells class="w-3.5 h-3.5 text-slate-400" />
                            </x-slot:icon>
                            <span>Kartu Stok</span>
                        </x-ui.button>

                        @if(auth()->user()->isAdmin())
                            {{-- Visible Action: Edit (Compact) --}}
                            <x-ui.button
                                wire:click="edit({{ $product->id }})"
                                variant="secondary"
                                size="xs"
                                title="Edit Barang"
                            >
                                <x-slot:icon>
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                </x-slot:icon>
                                <span>Edit</span>
                            </x-ui.button>

                            {{-- Overflow Menu (Three Dots) --}}
                            <x-ui.dropdown label="Opsi lainnya">
                                <x-ui.dropdown-item
                                    @click="
                                        closeMenu();
                                        window.SwalConfirm({
                                            title: '{{ $product->is_active ? 'Nonaktifkan Barang?' : 'Aktifkan Barang?' }}',
                                            text: '{{ $product->is_active ? 'Barang ' . addslashes($product->name) . ' tidak akan muncul pada pilihan transaksi baru saat dinonaktifkan.' : 'Barang ' . addslashes($product->name) . ' akan kembali aktif dan dapat dipilih pada transaksi.' }}',
                                            icon: 'question',
                                            confirmText: '{{ $product->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}',
                                            variant: '{{ $product->is_active ? 'amber' : 'emerald' }}',
                                            onConfirm: () => $wire.toggleActive({{ $product->id }})
                                        });
                                    "
                                >
                                    <x-slot:icon>
                                        @if($product->is_active)
                                            <x-heroicon-o-no-symbol class="w-4 h-4 text-amber-600" />
                                        @else
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600" />
                                        @endif
                                    </x-slot:icon>
                                    {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </x-ui.dropdown-item>

                                <div class="my-0.5 border-t border-slate-100"></div>

                                <x-ui.dropdown-item
                                    variant="danger"
                                    @click="
                                        closeMenu();
                                        window.SwalConfirm({
                                            title: 'Hapus Barang?',
                                            text: 'Data barang {{ addslashes($product->name) }} ({{ addslashes($product->sku) }}) akan dihapus permanen jika belum memiliki riwayat transaksi.',
                                            icon: 'warning',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'rose',
                                            onConfirm: () => $wire.delete({{ $product->id }})
                                        });
                                    "
                                >
                                    <x-slot:icon>
                                        <x-heroicon-o-trash class="w-4 h-4 text-rose-600" />
                                    </x-slot:icon>
                                    Hapus
                                </x-ui.dropdown-item>
                            </x-ui.dropdown>
                        @endif
                    </div>
                </x-ui.td>
            </x-ui.tr>
        @empty
            <tr>
                <td colspan="7">
                    @if($search !== '' || $selectedCategory !== '' || $stockStatusFilter !== '')
                        <x-ui.empty-state
                            title="Tidak Ada Barang yang Cocok"
                            description="Tidak ditemukan barang yang sesuai dengan kata kunci pencarian atau filter yang dipilih."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </x-slot:icon>

                            <x-ui.button
                                variant="outline"
                                size="xs"
                                wire:click="$set('search', ''); $set('selectedCategory', ''); $set('stockStatusFilter', '')"
                                class="text-blue-600 hover:text-blue-700"
                            >
                                <x-slot:icon>
                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                </x-slot:icon>
                                <span>Reset Filter</span>
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state
                            title="Tidak ada barang ditemukan."
                            description="Belum ada data produk yang terdaftar pada katalog inventaris."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-archive-box class="w-5 h-5" />
                            </x-slot:icon>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
        @endforelse

        @if($products->hasPages())
            <x-slot:footer>
                {{ $products->links() }}
            </x-slot:footer>
        @endif
    </x-ui.table>

    <x-ui.modal
        :open="$showModal"
        id="product-form-modal"
        :title="$editingProductId ? 'Edit Barang' : 'Tambah Barang Baru'"
        description="Lengkapi informasi barang untuk pengelolaan inventaris."
        maxWidth="lg"
        closeAction="closeModal"
        formAction="save"
        loadingTarget="save"
        wire:key="product-modal-{{ $editingProductId ?? 'create' }}"
    >
        <div class="space-y-4 sm:space-y-5">
            {{-- Row 1: Kode SKU & Barcode Pabrik --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input
                    id="modal_sku"
                    name="sku"
                    label="Kode SKU (Internal)"
                    wire:model="sku"
                    type="text"
                    placeholder="Contoh: PRD-001"
                    required
                />

                <x-ui.input
                    id="modal_barcode"
                    name="barcode"
                    label="Barcode Pabrik / EAN (Opsional)"
                    wire:model="barcode"
                    type="text"
                    placeholder="Contoh: 8992388101011"
                >
                    <x-slot:icon>
                        <x-heroicon-o-qr-code class="w-4 h-4 text-slate-400" />
                    </x-slot:icon>
                </x-ui.input>
            </div>

            {{-- Row 2: Nama Barang (Full Width) --}}
            <x-ui.input
                id="modal_name"
                name="name"
                label="Nama Barang"
                wire:model="name"
                type="text"
                placeholder="Contoh: Indomie Goreng Spesial 85g"
                required
            />

            {{-- Row 3: Kategori, Satuan & Batas Stok Minimum --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-searchable-select
                    id="modal_category_id"
                    name="category_id"
                    label="Kategori"
                    wire:model="category_id"
                    :options="$categories"
                    placeholder="Pilih Kategori"
                    searchPlaceholder="Cari kategori..."
                    :required="true"
                    :dropUp="true"
                />

                <x-searchable-select
                    id="modal_unit_id"
                    name="unit_id"
                    label="Satuan"
                    wire:model="unit_id"
                    :options="$units"
                    placeholder="Pilih Satuan"
                    searchPlaceholder="Cari satuan..."
                    :required="true"
                    :dropUp="true"
                />

                <x-ui.input
                    id="modal_minimum_stock"
                    name="minimum_stock"
                    label="Batas Minimum"
                    wire:model="minimum_stock"
                    type="number"
                    min="0"
                    placeholder="0"
                    required
                />
            </div>

            {{-- Row 4: Status Barang Aktif --}}
            <div class="pt-1">
                <x-ui.checkbox
                    id="modal_is_active"
                    name="is_active"
                    wire:model="is_active"
                    label="Status Barang Aktif"
                    description="Barang aktif dapat dipilih pada transaksi barang masuk, keluar, dan stock opname."
                    :card="true"
                />
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button
                variant="outline"
                size="md"
                wire:click="closeModal"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                Batal
            </x-ui.button>

            <x-ui.button
                type="submit"
                variant="primary"
                size="md"
                loadingTarget="save"
            >
                {{ $editingProductId ? 'Simpan Perubahan' : 'Simpan Barang' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
