<div class="space-y-5 min-w-0">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data pengguna sistem, penugasan peran (Admin / Petugas), dan status akses login.</p>
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
            <span>Tambah Pengguna</span>
        </x-ui.button>
    </div>

    {{-- Search Toolbar --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
        <div class="w-full sm:max-w-md">
            <x-ui.input
                id="user-search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau email pengguna..."
                :showClear="$search !== ''"
                clearAction="$set('search', '')"
            >
                <x-slot:icon>
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </x-slot:icon>
            </x-ui.input>
        </div>
    </div>

    {{-- Users Table --}}
    <x-ui.table loadingTarget="search,gotoPage,previousPage,nextPage,delete,toggleActive">
        <x-slot:head>
            <x-ui.th class="min-w-[180px]">Nama Pengguna</x-ui.th>
            <x-ui.th class="min-w-[200px]">Email</x-ui.th>
            <x-ui.th align="center" class="w-32 whitespace-nowrap">Peran</x-ui.th>
            <x-ui.th align="center" class="w-32 whitespace-nowrap">Status</x-ui.th>
            <x-ui.th align="right" class="w-36 whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse ($users as $user)
            <x-ui.tr wire:key="user-row-{{ $user->id }}">
                {{-- Nama Pengguna --}}
                <x-ui.td class="font-semibold text-[13px] text-slate-900 whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                        <span>{{ $user->name }}</span>
                        @if ($user->id === auth()->id())
                            <x-ui.badge variant="info" size="xs">Anda</x-ui.badge>
                        @endif
                    </div>
                </x-ui.td>

                {{-- Email --}}
                <x-ui.td class="font-mono text-xs text-slate-600 whitespace-nowrap">
                    {{ $user->email }}
                </x-ui.td>

                {{-- Peran --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    @if ($user->role === 'admin')
                        <x-ui.badge variant="info">Admin</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Petugas</x-ui.badge>
                    @endif
                </x-ui.td>

                {{-- Status --}}
                <x-ui.td align="center" class="whitespace-nowrap">
                    @if ($user->is_active)
                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Non-aktif</x-ui.badge>
                    @endif
                </x-ui.td>

                {{-- Row Actions --}}
                <x-ui.td align="right" class="whitespace-nowrap">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        <x-ui.button
                            wire:click="edit({{ $user->id }})"
                            variant="secondary"
                            size="xs"
                            title="Edit Pengguna"
                        >
                            <x-slot:icon>
                                <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                            </x-slot:icon>
                            <span>Edit</span>
                        </x-ui.button>

                        @if ($user->id !== auth()->id())
                            <x-ui.dropdown label="Opsi lainnya">
                                <x-ui.dropdown-item
                                    @click="
                                        closeMenu();
                                        window.SwalConfirm({
                                            title: '{{ $user->is_active ? 'Nonaktifkan Akun?' : 'Aktifkan Akun?' }}',
                                            text: '{{ $user->is_active ? 'Pengguna ' . addslashes($user->name) . ' tidak akan dapat login ke sistem saat dinonaktifkan.' : 'Pengguna ' . addslashes($user->name) . ' akan dapat kembali login ke sistem.' }}',
                                            icon: 'question',
                                            confirmText: '{{ $user->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}',
                                            variant: '{{ $user->is_active ? 'amber' : 'emerald' }}',
                                            onConfirm: () => $wire.toggleActive({{ $user->id }})
                                        });
                                    "
                                >
                                    <x-slot:icon>
                                        @if ($user->is_active)
                                            <x-heroicon-o-no-symbol class="w-4 h-4 text-amber-600" />
                                        @else
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600" />
                                        @endif
                                    </x-slot:icon>
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </x-ui.dropdown-item>

                                <div class="my-0.5 border-t border-slate-100"></div>

                                <x-ui.dropdown-item
                                    variant="danger"
                                    @click="
                                        closeMenu();
                                        window.SwalConfirm({
                                            title: 'Hapus Akun Pengguna?',
                                            text: 'Akun {{ addslashes($user->name) }} ({{ addslashes($user->email) }}) akan dihapus permanen jika belum memiliki riwayat pencatatan transaksi.',
                                            icon: 'warning',
                                            confirmText: 'Ya, Hapus',
                                            variant: 'rose',
                                            onConfirm: () => $wire.delete({{ $user->id }})
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
                <td colspan="5">
                    @if ($search !== '')
                        <x-ui.empty-state
                            title="Tidak Ada Pengguna yang Cocok"
                            description="Tidak ditemukan pengguna sistem yang sesuai dengan kata kunci pencarian Anda."
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
                            title="Tidak ada pengguna ditemukan."
                            description="Belum ada data staf atau akun pengguna yang terdaftar pada sistem inventaris."
                        >
                            <x-slot:icon>
                                <x-heroicon-o-user-group class="w-5 h-5" />
                            </x-slot:icon>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
        @endforelse

        @if ($users->hasPages())
            <x-slot:footer>
                {{ $users->links() }}
            </x-slot:footer>
        @endif
    </x-ui.table>

    {{-- Add / Edit User Modal --}}
    <x-ui.modal
        :open="$showModal"
        id="user-form-modal"
        :title="$editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"
        description="Kelola kredensial akun, peran wewenang (Admin / Petugas), dan status akses login."
        maxWidth="md"
        closeAction="closeModal"
        formAction="save"
        loadingTarget="save"
        wire:key="user-modal-{{ $editingUserId ?? 'create' }}"
    >
        <div class="space-y-4">
            <x-ui.input
                id="user_name"
                name="name"
                label="Nama Lengkap"
                wire:model="name"
                type="text"
                placeholder="Nama staf atau petugas"
                required
            />

            <x-ui.input
                id="user_email"
                name="email"
                label="Alamat Email"
                wire:model="email"
                type="email"
                placeholder="email@perusahaan.com"
                required
            />

            <x-ui.input
                id="user_password"
                name="password"
                :label="'Kata Sandi ' . ($editingUserId ? '(Kosongkan jika tidak diubah)' : '*')"
                wire:model="password"
                type="password"
                placeholder="Minimal 6 karakter"
                :required="!$editingUserId"
            />

            <x-searchable-select
                id="user_role"
                name="role"
                label="Peran Akses (Role)"
                wire:model="role"
                :options="[
                    ['value' => 'petugas', 'label' => 'Petugas (Transaksi Masuk/Keluar, Stok & Laporan)'],
                    ['value' => 'admin', 'label' => 'Admin (Akses Penuh Master Data, Users, Transaksi & Laporan)']
                ]"
                placeholder="Pilih Role"
                searchPlaceholder="Cari role..."
                :dropUp="true"
                required
            />

            <x-ui.checkbox
                id="user_is_active"
                name="is_active"
                wire:model="is_active"
                label="Status Akun Aktif"
                description="Jika dinonaktifkan, pengguna tidak dapat login ke sistem inventaris."
                :card="true"
            />
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
                {{ $editingUserId ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
