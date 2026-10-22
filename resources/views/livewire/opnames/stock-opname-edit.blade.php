<div class="space-y-6 min-w-0">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-pencil-square class="w-5 h-5 stroke-[2]" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Berita Acara Stock Opname</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Koreksi Audit
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Nomor Dokumen: <span class="font-mono font-bold text-blue-700">{{ $opname->opname_no }}</span>
                </p>
            </div>
        </div>

        <x-ui.button
            href="{{ route('opnames.show', $opname->id) }}"
            variant="outline"
            size="sm"
            class="self-start sm:self-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5 text-slate-500 mr-1.5" />
            </x-slot:icon>
            Kembali ke Detail
        </x-ui.button>
    </div>

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <x-heroicon-s-x-circle class="w-4 h-4 text-rose-600 shrink-0" />
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if ($errors->has('items'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <x-heroicon-s-x-circle class="w-4 h-4 text-rose-600 shrink-0" />
        <span class="font-medium">{{ $errors->first('items') }}</span>
    </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Section 1: Informasi Dokumen Opname -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shrink-0">
                    <x-heroicon-o-document-text class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Dokumen Opname</h2>
                    <p class="text-[11px] text-slate-400">Data tanggal pemeriksaan fisik riil di gudang dan catatan berita acara.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.input
                        id="opname_date"
                        label="Tanggal Pemeriksaan Fisik"
                        wire:model="opname_date"
                        type="date"
                        :required="true"
                    />
                </div>

                <div>
                    <x-ui.input
                        id="opname_note"
                        label="Catatan / Keterangan Berita Acara"
                        wire:model="note"
                        type="text"
                        placeholder="Contoh: Koreksi audit fisik berkala..."
                    />
                </div>
            </div>
        </div>

        <!-- Section 2: Daftar Barang yang Diperiksa -->
        @include('livewire.opnames.stock-opname-items', [
            'rowPrefix' => 'stock-opname-edit',
            'removalText' => 'Baris pemeriksaan barang ini akan dihapus dari koreksi opname.',
        ])

        <!-- Section 3: Footer Actions -->
        <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 pt-2">
            <x-ui.button
                href="{{ route('opnames.show', $opname->id) }}"
                variant="outline"
                size="md"
                class="w-full sm:w-auto text-center"
            >
                Batal
            </x-ui.button>

            <x-ui.button
                @click="window.SwalConfirm({
                    title: 'Perbarui Berita Acara Opname?',
                    text: 'Perubahan pada dokumen {{ addslashes($opname->opname_no) }} akan disimpan dan saldo stok sistem akan direkonsiliasi ulang.',
                    icon: 'question',
                    confirmText: 'Ya, Perbarui & Sesuaikan',
                    variant: 'indigo',
                    onConfirm: () => $wire.save()
                })"
                type="button"
                variant="primary"
                size="md"
                loadingTarget="save"
                class="w-full sm:w-auto"
            >
                <x-slot:icon>
                    <x-heroicon-o-check class="w-4 h-4 mr-1.5" />
                </x-slot:icon>
                Perbarui & Sesuaikan Stok Sistem
            </x-ui.button>
        </div>
    </form>
</div>
