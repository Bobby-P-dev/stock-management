<div class="space-y-6 min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 stroke-[2]" />
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Stock Opname & Penyesuaian Stok</h1>
                <p class="text-xs text-slate-500 mt-1">Rekapitulasi pemeriksaan fisik riil inventaris barang terhadap pencatatan sistem secara berkala.</p>
            </div>
        </div>

        <x-ui.button
            href="{{ route('opnames.create') }}"
            variant="primary"
            size="sm"
            class="w-full sm:w-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-plus class="w-4 h-4 mr-1.5 stroke-[2]" />
            </x-slot:icon>
            Catat Stock Opname Baru
        </x-ui.button>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <div class="sm:col-span-2 lg:col-span-6 min-w-0">
                <x-ui.input
                    label="Cari Dokumen / Catatan"
                    wire:model.live.debounce.300ms="search"
                    placeholder="SOP-..., nama petugas, catatan..."
                >
                    <x-slot:icon>
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400" />
                    </x-slot:icon>
                </x-ui.input>
            </div>

            <div class="lg:col-span-3 min-w-0">
                <x-ui.input
                    label="Dari Tanggal"
                    wire:model.live="startDate"
                    type="date"
                />
            </div>

            <div class="lg:col-span-3 min-w-0">
                <x-ui.input
                    label="Sampai Tanggal"
                    wire:model.live="endDate"
                    type="date"
                />
            </div>
        </div>

    </div>

    <x-ui.table loadingTarget="search,startDate,endDate,gotoPage,previousPage,nextPage">
        <x-slot:head>
            <x-ui.th class="w-48 whitespace-nowrap">No. Dokumen Opname</x-ui.th>
            <x-ui.th class="w-32 whitespace-nowrap">Tanggal Fisik</x-ui.th>
            <x-ui.th align="center" class="w-36 whitespace-nowrap">Jumlah Barang</x-ui.th>
            <x-ui.th class="min-w-[220px]">Catatan / Berita Acara</x-ui.th>
            <x-ui.th class="min-w-[160px]">Petugas Pemeriksa</x-ui.th>
            <x-ui.th align="right" class="w-64 whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse($opnames as $op)
        <tr wire:key="opname-row-{{ $op->id }}" class="hover:bg-slate-50/60 transition">
            <x-ui.td class="font-mono font-bold text-slate-900 tracking-tight whitespace-nowrap">
                {{ $op->opname_no }}
            </x-ui.td>

            <x-ui.td class="text-slate-600 whitespace-nowrap tabular-nums">
                {{ $op->opname_date->format('d/m/Y') }}
            </x-ui.td>

            <x-ui.td align="center" class="whitespace-nowrap">
                <x-ui.badge variant="neutral" size="md">
                    {{ $op->items->count() }} jenis barang
                </x-ui.badge>
            </x-ui.td>

            <x-ui.td class="text-slate-600">
                <p class="max-w-md whitespace-normal break-words leading-relaxed text-xs">
                    {{ $op->note ?: '-' }}
                </p>
            </x-ui.td>

            <x-ui.td>
                <div class="text-slate-800 font-medium break-words">{{ $op->creator->name }}</div>
                <div class="text-[11px] capitalize text-slate-500 mt-0.5">{{ $op->creator->role }}</div>
            </x-ui.td>

            <x-ui.td align="right" class="whitespace-nowrap">
                <div class="inline-flex items-center justify-end gap-1.5">
                    <x-ui.button
                        href="{{ route('opnames.show', $op->id) }}"
                        variant="outline"
                        size="xs"
                        title="Lihat Berita Acara"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-eye class="w-3.5 h-3.5 mr-1 text-slate-500" />
                        </x-slot:icon>
                        Rincian
                    </x-ui.button>

                    <x-ui.button
                        href="{{ route('opnames.edit', $op->id) }}"
                        variant="secondary"
                        size="xs"
                        title="Edit Dokumen Opname"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5 mr-1" />
                        </x-slot:icon>
                        Edit
                    </x-ui.button>

                    <x-ui.button
                        @click="window.SwalConfirm({
                            title: 'Hapus Dokumen Opname?',
                            text: 'Dokumen Stock Opname {{ addslashes($op->opname_no) }} akan dihapus dan seluruh penyesuaian stok barang pada dokumen ini akan dikembalikan.',
                            icon: 'warning',
                            confirmText: 'Ya, Hapus & Batalkan Stok',
                            variant: 'rose',
                            onConfirm: () => $wire.delete({{ $op->id }})
                        })"
                        variant="danger"
                        size="xs"
                        title="Hapus Dokumen Opname"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-trash class="w-3.5 h-3.5 mr-1" />
                        </x-slot:icon>
                        Hapus
                    </x-ui.button>
                </div>
            </x-ui.td>
        </tr>
        @empty
        <tr>
            <td colspan="6">
                <x-ui.empty-state
                    title="Belum ada riwayat dokumen Stock Opname"
                    description="Dokumen hasil audit fisik gudang akan tercatat di sini secara otomatis."
                >
                    <x-slot:icon>
                        <x-heroicon-o-inbox class="w-6 h-6 stroke-[1.5]" />
                    </x-slot:icon>
                </x-ui.empty-state>
            </td>
        </tr>
        @endforelse

        @if($opnames->hasPages())
        <x-slot:footer>
            {{ $opnames->links() }}
        </x-slot:footer>
        @endif
    </x-ui.table>
</div>
