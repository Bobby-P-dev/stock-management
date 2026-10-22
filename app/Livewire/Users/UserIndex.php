<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kelola Pengguna - Berkah Mandiri Inventory')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'petugas';

    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'email', 'password']);
    }

    public function updatedShowModal(bool $value): void
    {
        if (! $value) {
            $this->resetValidation();
        }
    }

    protected function rules(): array
    {
        $passwordRule = $this->editingUserId ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'];

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => $passwordRule,
            'role' => ['required', 'in:admin,petugas'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingUserId) {
            $rules['email'][] = Rule::unique('users', 'email')->ignore($this->editingUserId);
        } else {
            $rules['email'][] = Rule::unique('users', 'email');
        }

        return $rules;
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'email', 'password']);
        $this->role = 'petugas';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(User $user): void
    {
        $this->resetValidation();
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ];
            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }
            $user->update($data);
            $message = "Data pengguna '{$user->name}' berhasil diperbarui.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Pengguna Diperbarui',
                'text' => $message,
                'toast' => true,
            ]);
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ]);
            $message = "Pengguna baru '{$user->name}' berhasil ditambahkan.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Pengguna Ditambahkan',
                'text' => $message,
                'toast' => true,
            ]);
        }

        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['editingUserId', 'name', 'email', 'password']);
    }

    public function toggleActive(User $user): void
    {
        if ($user->id === auth()->id()) {
            $errorMsg = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Aksi Ditolak',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $statusLabel = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Status akun '{$user->name}' berhasil {$statusLabel}.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Status Akun Diubah',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $errorMsg = 'Anda tidak dapat menghapus akun yang sedang Anda gunakan.';
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Aksi Ditolak',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        if ($user->stockTransactions()->exists() || $user->stockOpnames()->exists()) {
            $errorMsg = "Pengguna '{$user->name}' sudah memiliki riwayat pencatatan transaksi atau stock opname. Gunakan tombol Nonaktifkan agar log audit tetap terjaga.";
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $userName = $user->name;
        $user->delete();

        $message = "Akun pengguna '{$userName}' berhasil dihapus secara permanen.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Pengguna Dihapus',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $users = User::when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%');
        })
            ->latest('id')
            ->paginate(10);

        return view('livewire.users.user-index', [
            'users' => $users,
        ]);
    }
}
