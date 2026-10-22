<div class="space-y-5 min-w-0">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Mitra Supplier</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar vendor penyedia barang masuk (Stock In) dan kontak penanggung jawab.</p>
        </div>

        <x-ui.button
            wire:click="create"
            variant="primary"
            size="md"
            class="self-start sm:self-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-plus class="w-4 h-4" />
            </x-slot:icon>
            <span>Tambah Supplier</span>
        </x-ui.button>
    </div>

    {{-- Search Toolbar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
        <div class="w-full sm:max-w-md">
            <x-ui.input
                id="supplier-search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kode, nama, atau no telepon supplier..."
                :showClear="$search !== ''"
                clearAction="$set('search', '')"
            >
                <x-slot:icon>
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </x-slot:icon>
            </x-ui.input>
        </div>
    </div>

    {{-- Suppliers Table --}}
    <x-ui.table loadingTarget="search,gotoPage,previousPage,nextPage,delete,toggleActive">
        <x-slot:head>
            <x-ui.th class="w-32 whitespace-nowrap">Kode</x-ui.th>
            <x-ui.th class="min-w-[180px]">Nama Supplier</x-ui.th>
            <x-ui.th class="min-w-[170px]">Kontak</x-ui.th>
            <x-ui.th class="min-w-[200px]">Alamat</x-ui.th>
            <x-ui.th align="right" class="w-36 whitespace-nowrap">Faktur Masuk</x-ui.th>
            <x-ui.th align="center" class="w-32 whitespace-nowrap">Status</x-ui.th>
            <x-ui.th align="right" class="w-36 whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse ($suppliers as $supplier)
            <x-ui.tr wire:key="supplier-row-{{ $supplier->id }}">
                {{-- Kode --}}
                <x-ui.td class="whitespace-nowrap">
                    <x-ui.badge variant="mono" size="code">
                        {{ $supplier->code }}
                    </x-ui.badge>
                </x-ui.td>

                {{-- Nama Supplier --}}
                <x-ui.td class="font-semibold text-[13px] text-slate-900">
                    {{ $supplier->name }}
                </x-ui.td>

                {{-- Kontak --}}
                <x-ui.td>
                    @if ($supplier->phone || $supplier->email)
                        <div class="space-y-0.5 leading-snug">
                            @if ($supplier->phone)
                                <div class="text-xs font-medium text-slate-700 tabular-nums">{{ $supplier->phone }}</div>
                            @endif
                            @if ($supplier->email)
                                <div class="text-[11px] text-slate-500 truncate max-w-[200px]">{{ $supplier->email }}</div>
                            @endif
                        </div>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </x-ui.td>

                {{-- Alamat --}}
                <x-ui.td class="text-slate-600 text-xs leading-relaxed max-w-xs whitespace-normal break-words">
                    {{ $supplier->address ?: '-' }}
                </x-ui.td>

                {{-- Faktur Masuk --}}
                <x-ui.td align="right" :numeric="true">
                    <span class="font-bold text-slate-900">{{ number_format($supplier->stock_transactions_count, 0, ',', '.') }}</span>
                    <span class="text-slate-500 ml-0.5">transaksi</span>
                </x-ui.td>

                {{-- Status --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    @if ($supplier->is_active)
                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Non-aktif</x-ui.badge>
                    @endif
                </x-ui.td>

                {{-- Row Actions --}}
                <x-ui.td align="right" class="whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        <x-ui.button
                            wire:click="edit({{ $supplier->id }})"
                            variant="secondary"
                            size="xs"
                            title="Edit Supplier"
                        >
                            <x-slot:icon>
                                <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                            </x-slot:icon>
                            <span>Edit</span>
                        </x-ui.button>

                        <x-ui.dropdown label="Opsi lainnya">
                            <x-ui.dropdown-item
                                @click="
                                    closeMenu();
                                    window.SwalConfirm({
                                        title: '{{ $supplier->is_active ? 'Nonaktifkan Supplier?' : 'Aktifkan Supplier?' }}',
                                        text: '{{ $supplier->is_active ? 'Supplier ' . addslashes($supplier->name) . ' tidak akan muncul pada form Barang Masuk saat dinonaktifkan.' : 'Supplier ' . addslashes($supplier->name) . ' akan kembali aktif.' }}',
                                        icon: 'question',
                                        confirmText: '{{ $supplier->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}',
                                        variant: '{{ $supplier->is_active ? 'amber' : 'emerald' }}',
                                        onConfirm: () => $wire.toggleActive({{ $supplier->id }})
                                    });
                                "
                            >
                                <x-slot:icon>
                                    @if ($supplier->is_active)
                                        <x-heroicon-o-no-symbol class="w-4 h-4 text-amber-600" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600" />
                                    @endif
                                </x-slot:icon>
                                {{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </x-ui.dropdown-item>

                            <div class="my-0.5 border-t border-slate-100"></div>

                            <x-ui.dropdown-item
                                variant="danger"
                                @click="
                                    closeMenu();
                                    window.SwalConfirm({
                                        title: 'Hapus Supplier?',
                                        text: 'Data supplier {{ addslashes($supplier->name) }} ({{ addslashes($supplier->code) }}) akan dihapus permanen jika belum memiliki riwayat transaksi.',
                                        icon: 'warning',
                                        confirmText: 'Ya, Hapus',
                                        variant: 'rose',
                                        onConfirm: () => $wire.delete({{ $supplier->id }})
                                    });
                                "
                            >
                                <x-slot:icon>
                                    <x-heroicon-o-trash class="w-4 h-4 text-rose-600" />
                                </x-slot:icon>
                                Hapus
                            </x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </x-ui.td>
            </x-ui.tr>
        @empty
            <tr>
                <td colspan="7">
                    @if ($search !== '')
                        <x-ui.empty-state
                            title="Tidak Ada Supplier yang Cocok"
                            description="Tidak ditemukan mitra supplier yang sesuai dengan kata kunci pencarian Anda."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </x-slot:icon>

                            <x-ui.button
                                variant="outline"
                                size="xs"
                                wire:click="$set('search', '')"
                                class="text-blue-600 hover:text-blue-700"
                            >
                                <x-slot:icon>
                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                </x-slot:icon>
                                <span>Reset Pencarian</span>
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state
                            title="Tidak ada supplier ditemukan."
                            description="Belum ada data mitra supplier yang terdaftar pada sistem inventaris."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-truck class="w-5 h-5" />
                            </x-slot:icon>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
        @endforelse

        @if ($suppliers->hasPages())
            <x-slot:footer>
                {{ $suppliers->links() }}
            </x-slot:footer>
        @endif
    </x-ui.table>

    {{-- Add / Edit Supplier Modal --}}
    <x-ui.modal
        :open="$showModal"
        id="supplier-form-modal"
        :title="$editingSupplierId ? 'Edit Supplier' : 'Tambah Supplier Baru'"
        description="Lengkapi informasi mitra pemasok dan kontak penanggung jawab untuk pengadaan barang masuk."
        maxWidth="lg"
        closeAction="closeModal"
        formAction="save"
        loadingTarget="save"
        wire:key="supplier-modal-{{ $editingSupplierId ?? 'create' }}"
    >
        <div class="space-y-4 sm:space-y-5">
            {{-- Row 1: Supplier Identity --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input
                    id="sup_code"
                    name="code"
                    label="Kode Supplier"
                    wire:model="code"
                    type="text"
                    placeholder="Contoh: SUP-001"
                    class="uppercase font-mono"
                    required
                />

                <x-ui.input
                    id="sup_name"
                    name="name"
                    label="Nama Supplier"
                    wire:model="name"
                    type="text"
                    placeholder="Contoh: PT Sinar Abadi"
                    required
                />
            </div>

            {{-- Row 2: Contact Information --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input
                    id="sup_phone"
                    name="phone"
                    label="No. Telepon / WhatsApp"
                    wire:model="phone"
                    type="text"
                    placeholder="08123456789"
                />

                <x-ui.input
                    id="sup_email"
                    name="email"
                    label="Alamat Email"
                    wire:model="email"
                    type="email"
                    placeholder="info@perusahaan.com"
                />
            </div>

            {{-- Row 3: Address Information --}}
            <x-ui.textarea
                id="sup_address"
                name="address"
                label="Alamat Kantor / Gudang"
                wire:model="address"
                rows="3"
                placeholder="Alamat lengkap supplier..."
            />

            {{-- Row 4: Active Status --}}
            <div class="pt-1">
                <x-ui.checkbox
                    id="sup_is_active"
                    name="is_active"
                    wire:model="is_active"
                    label="Status Supplier Aktif"
                    description="Supplier aktif dapat dipilih pada pencatatan faktur transaksi barang masuk (Stock In)."
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
                {{ $editingSupplierId ? 'Simpan Perubahan' : 'Simpan Supplier' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
