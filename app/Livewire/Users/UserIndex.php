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
        $this->reset(['editingUserId', 'name', 'email', 'password']);
        $this->role = 'petugas';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(User $user): void
    {
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->is_active = $user->is_active;
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
            session()->flash('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => $validated['is_active'],
            ]);
            session()->flash('success', "Pengguna baru '{$user->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['editingUserId', 'name', 'email', 'password']);
    }

    public function toggleActive(User $user): void
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        session()->flash('success', "Status akun '{$user->name}' berhasil diubah.");
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
