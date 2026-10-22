<div class="bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4 shadow-2xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-600 shrink-0">
                <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
            </div>
            <div>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Barang yang Diperiksa</h2>
                <p class="text-[11px] text-slate-400">Pilih barang dan masukkan jumlah fisik nyata di rak. Sistem akan menghitung selisih otomatis.</p>
            </div>
        </div>

        <x-ui.button
            wire:click="addItem"
            type="button"
            variant="secondary"
            size="sm"
            loadingTarget="addItem"
            class="self-start sm:self-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-plus class="w-3.5 h-3.5 stroke-[2.5] mr-1.5" />
            </x-slot:icon>
            Tambah Baris Barang
        </x-ui.button>
    </div>

    @error('items')
        <x-ui.field-error :messages="$message" />
    @enderror

    <!-- Table Container with horizontal scroll on small screens & dropdown space -->
    <div class="overflow-x-auto pb-48 -mx-5 sm:-mx-6 px-5 sm:px-6">
        <table class="w-full min-w-[1020px] text-left text-xs border-collapse">
            <thead>
                <tr class="text-slate-600 border-b border-slate-200/80 uppercase tracking-wider text-[11px] bg-slate-50/70">
                    <th class="py-3 px-3 text-center w-12 font-semibold">No</th>
                    <th class="py-3 px-3 font-semibold min-w-[280px]">Barang <span class="text-rose-500 normal-case">*</span></th>
                    <th class="py-3 px-3 font-semibold text-center w-28 whitespace-nowrap">Stok Sistem</th>
                    <th class="py-3 px-3 font-semibold text-center w-32 whitespace-nowrap">Stok Fisik <span class="text-rose-500 normal-case">*</span></th>
                    <th class="py-3 px-3 font-semibold text-center w-32 whitespace-nowrap">Selisih</th>
                    <th class="py-3 px-3 font-semibold min-w-[200px] w-52">Alasan Selisih</th>
                    <th class="py-3 px-3 font-semibold min-w-[200px] w-56">Catatan Khusus</th>
                    <th class="py-3 px-3 font-semibold text-center w-14">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($items as $index => $item)
                <tr
                    wire:key="{{ $rowPrefix }}-row-{{ $index }}-{{ $item['product_id'] ?? '' }}"
                    style="z-index: {{ 50 - $index }};"
                    class="hover:bg-slate-50/40 transition relative"
                >
                    <td class="py-3 px-3 text-center text-slate-500 font-mono align-top pt-4">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold">
                            {{ $index + 1 }}
                        </span>
                    </td>

                    <td class="py-3 px-3 align-top">
                        <x-searchable-select
                            wire:key="{{ $rowPrefix }}-item-{{ $index }}-{{ $item['product_id'] ?? '' }}"
                            wire:model.live="items.{{ $index }}.product_id"
                            :options="$products"
                            placeholder="-- Pilih Barang --"
                            searchPlaceholder="Cari nama atau SKU..."
                        />
                        @error("items.{$index}.product_id")
                            <span class="mt-1 text-[11px] font-medium text-rose-600 block">{{ $message }}</span>
                        @enderror
                    </td>

                    <td class="py-3 px-3 align-top text-center">
                        <div class="inline-flex items-center justify-center w-full h-[42px] px-2.5 rounded-xl bg-slate-100/90 border border-slate-200/70 text-slate-700 font-mono font-bold text-xs" title="Saldo sistem saat ini">
                            {{ $item['system_stock'] }}
                        </div>
                    </td>

                    <td class="py-3 px-3 align-top text-center">
                        <x-ui.input
                            wire:key="{{ $rowPrefix }}-physical-{{ $index }}"
                            wire:model.live.debounce.300ms="items.{{ $index }}.physical_stock"
                            type="number"
                            min="0"
                            required
                            class="text-center font-mono font-bold"
                        />
                    </td>

                    <td class="py-3 px-3 align-top text-center">
                        @php $diff = $item['difference'] ?? 0; @endphp
                        <div class="inline-flex items-center justify-center w-full h-[42px] px-2.5 rounded-xl border text-xs font-bold font-mono whitespace-nowrap {{ $diff === 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($diff < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                            @if($diff === 0)
                                0 (Cocok)
                            @elseif($diff < 0)
                                {{ $diff }} (Kurang)
                            @else
                                +{{ $diff }} (Lebih)
                            @endif
                        </div>
                    </td>

                    <td class="py-3 px-3 align-top">
                        <x-searchable-select
                            wire:key="{{ $rowPrefix }}-reason-{{ $index }}"
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
                    </td>

                    <td class="py-3 px-3 align-top">
                        <x-ui.input
                            wire:key="{{ $rowPrefix }}-notes-{{ $index }}"
                            wire:model="items.{{ $index }}.item_notes"
                            type="text"
                            placeholder="Keterangan..."
                        />
                    </td>

                    <td class="py-3 px-3 align-top text-center">
                        @if(count($items) > 1)
                            <button
                                @click="window.SwalConfirm({
                                    title: 'Hapus Baris #{{ $index + 1 }}?',
                                    text: '{{ $removalText }}',
                                    icon: 'warning',
                                    confirmText: 'Ya, Hapus Baris',
                                    variant: 'rose',
                                    onConfirm: () => $wire.removeItem({{ $index }})
                                })"
                                type="button"
                                class="w-[42px] h-[42px] inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                title="Hapus baris barang ini"
                            >
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        @else
                            <div class="w-[42px] h-[42px] inline-flex items-center justify-center text-slate-300">
                                <span class="text-xs">-</span>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sub-footer: Tambah Baris & Summary -->
    <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <x-ui.button
            wire:click="addItem"
            type="button"
            variant="ghost"
            size="sm"
            loadingTarget="addItem"
            class="self-start sm:self-auto"
        >
            <x-slot:icon>
                <x-heroicon-o-plus-circle class="w-4 h-4 mr-1.5 text-blue-600" />
            </x-slot:icon>
            Tambah Barang Lainnya
        </x-ui.button>

        <p class="text-xs text-slate-500">
            Total item diperiksa: <strong class="text-slate-800 font-bold font-mono">{{ count($items) }}</strong> barang
        </p>
    </div>
</div>
