<div class="space-y-5 min-w-0">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Satuan Barang</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar satuan ukuran untuk kuantitas produk (PCS, BOX, UNIT, dll).</p>
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
            <span>Tambah Satuan</span>
        </x-ui.button>
    </div>

    {{-- Search Toolbar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
        <div class="w-full sm:max-w-md">
            <x-ui.input
                id="unit-search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau simbol satuan..."
                :showClear="$search !== ''"
                clearAction="$set('search', '')"
            >
                <x-slot:icon>
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </x-slot:icon>
            </x-ui.input>
        </div>
    </div>

    {{-- Units Table --}}
    <x-ui.table loadingTarget="search,gotoPage,previousPage,nextPage,delete,toggleActive">
        <x-slot:head>
            <x-ui.th>Nama Satuan</x-ui.th>
            <x-ui.th align="center" class="w-36 whitespace-nowrap">Simbol</x-ui.th>
            <x-ui.th align="right" class="w-40 whitespace-nowrap">Jumlah Barang</x-ui.th>
            <x-ui.th align="center" class="w-32 whitespace-nowrap">Status</x-ui.th>
            <x-ui.th align="right" class="w-36 whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse ($units as $unit)
            <x-ui.tr wire:key="unit-row-{{ $unit->id }}">
                {{-- Nama Satuan --}}
                <x-ui.td class="font-semibold text-[13px] text-slate-900">
                    {{ $unit->name }}
                </x-ui.td>

                {{-- Simbol --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    <x-ui.badge variant="mono" size="code">
                        {{ $unit->symbol }}
                    </x-ui.badge>
                </x-ui.td>

                {{-- Jumlah Barang --}}
                <x-ui.td align="right" :numeric="true">
                    <span class="font-bold text-slate-900">{{ number_format($unit->products_count, 0, ',', '.') }}</span>
                    <span class="text-slate-500 ml-0.5">item</span>
                </x-ui.td>

                {{-- Status --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    @if ($unit->is_active)
                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Non-aktif</x-ui.badge>
                    @endif
                </x-ui.td>

                {{-- Row Actions --}}
                <x-ui.td align="right" class="whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        <x-ui.button
                            wire:click="edit({{ $unit->id }})"
                            variant="secondary"
                            size="xs"
                            title="Edit Satuan"
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
                                        title: '{{ $unit->is_active ? 'Nonaktifkan Satuan?' : 'Aktifkan Satuan?' }}',
                                        text: '{{ $unit->is_active ? 'Satuan ' . addslashes($unit->name) . ' (' . addslashes($unit->symbol) . ') tidak akan muncul pada form barang baru saat dinonaktifkan.' : 'Satuan ' . addslashes($unit->name) . ' akan kembali aktif.' }}',
                                        icon: 'question',
                                        confirmText: '{{ $unit->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}',
                                        variant: '{{ $unit->is_active ? 'amber' : 'emerald' }}',
                                        onConfirm: () => $wire.toggleActive({{ $unit->id }})
                                    });
                                "
                            >
                                <x-slot:icon>
                                    @if ($unit->is_active)
                                        <x-heroicon-o-no-symbol class="w-4 h-4 text-amber-600" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600" />
                                    @endif
                                </x-slot:icon>
                                {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </x-ui.dropdown-item>

                            <div class="my-0.5 border-t border-slate-100"></div>

                            <x-ui.dropdown-item
                                variant="danger"
                                @click="
                                    closeMenu();
                                    window.SwalConfirm({
                                        title: 'Hapus Satuan?',
                                        text: 'Satuan {{ addslashes($unit->name) }} ({{ addslashes($unit->symbol) }}) akan dihapus permanen jika belum digunakan oleh data barang.',
                                        icon: 'warning',
                                        confirmText: 'Ya, Hapus',
                                        variant: 'rose',
                                        onConfirm: () => $wire.delete({{ $unit->id }})
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
                <td colspan="5">
                    @if ($search !== '')
                        <x-ui.empty-state
                            title="Tidak Ada Satuan yang Cocok"
                            description="Tidak ditemukan satuan barang yang sesuai dengan kata kunci pencarian Anda."
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
                            title="Tidak ada satuan ditemukan."
                            description="Belum ada satuan ukuran barang yang terdaftar pada sistem inventaris."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-scale class="w-5 h-5" />
                            </x-slot:icon>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
        @endforelse

        @if ($units->hasPages())
            <x-slot:footer>
                {{ $units->links() }}
            </x-slot:footer>
        @endif
    </x-ui.table>

    {{-- Add / Edit Satuan Modal --}}
    <x-ui.modal
        :open="$showModal"
        id="unit-form-modal"
        :title="$editingUnitId ? 'Edit Satuan' : 'Tambah Satuan Baru'"
        description="Lengkapi informasi satuan ukuran untuk pengelolaan kuantitas inventaris."
        maxWidth="md"
        closeAction="closeModal"
        formAction="save"
        loadingTarget="save"
        wire:key="unit-modal-{{ $editingUnitId ?? 'create' }}"
    >
        <div class="space-y-4 sm:space-y-5">
            <x-ui.input
                id="unit_name"
                name="name"
                label="Nama Satuan"
                wire:model="name"
                type="text"
                placeholder="Contoh: Pieces"
                required
            />

            <x-ui.input
                id="unit_symbol"
                name="symbol"
                label="Simbol / Singkatan"
                wire:model="symbol"
                type="text"
                placeholder="Contoh: PCS"
                class="uppercase"
                required
            />

            <div class="pt-1">
                <x-ui.checkbox
                    id="unit_is_active"
                    name="is_active"
                    wire:model="is_active"
                    label="Status Satuan Aktif"
                    description="Satuan aktif dapat dipilih saat menambahkan atau memperbarui data barang."
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
                {{ $editingUnitId ? 'Simpan Perubahan' : 'Simpan Satuan' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
