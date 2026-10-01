<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data pengguna sistem, penugasan peran (Admin / Petugas), dan status akses.</p>
        </div>

        <button
            wire:click="create"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer self-start sm:self-auto"
        >
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Pengguna
        </button>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-x-circle class="w-4 h-4 text-rose-600 shrink-0" />
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Search box -->
    <div class="max-w-md">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari nama atau email pengguna..."
                class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 shadow-sm transition"
            >
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 bg-slate-50/70 border-b border-slate-100">
                        <th class="py-3 px-4 font-semibold">Nama</th>
                        <th class="py-3 px-4 font-semibold">Email</th>
                        <th class="py-3 px-4 font-semibold text-center">Peran</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-semibold text-slate-900">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                            <span class="ml-1 text-[10px] text-indigo-600 font-bold">(Anda)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono">{{ $user->email }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize {{ $user->role === 'admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($user->is_active)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aktif
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Non-aktif
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <button
                                wire:click="edit({{ $user->id }})"
                                type="button"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 font-semibold text-[11px] transition cursor-pointer"
                            >
                                <x-heroicon-o-pencil-square class="w-3.5 h-3.5 text-indigo-600" />
                                Edit
                            </button>
                            @if($user->id !== auth()->id())
                            <button
                                wire:click="toggleActive({{ $user->id }})"
                                type="button"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-slate-600 bg-slate-100 hover:bg-slate-200 font-medium text-[11px] transition cursor-pointer"
                            >
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">
                            Tidak ada pengguna ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">
                    {{ $editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
                </h3>
                <button wire:click="$set('showModal', false)" type="button" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="Nama staf/petugas"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('name') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
                    <input
                        wire:model="email"
                        type="email"
                        placeholder="email@perusahaan.com"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('email') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kata Sandi {{ $editingUserId ? '(Kosongkan jika tidak diubah)' : '*' }}
                    </label>
                    <input
                        wire:model="password"
                        type="password"
                        placeholder="Minimal 6 karakter"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                    @error('password') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran Akses (Role) *</label>
                    <select
                        wire:model="role"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                    >
                        <option value="petugas">Petugas (Transaksi Masuk/Keluar, Stok & Laporan)</option>
                        <option value="admin">Admin (Akses Penuh Master Data, Users, Transaksi & Laporan)</option>
                    </select>
                    @error('role') <span class="text-[11px] text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input wire:model="is_active" type="checkbox" id="user_is_active" class="rounded border-slate-300 text-indigo-600">
                    <label for="user_is_active" class="text-xs text-slate-700 cursor-pointer">Status Akun Aktif</label>
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
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
