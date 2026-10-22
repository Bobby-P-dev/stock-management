<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Data Supplier - Berkah Mandiri Inventory')]
class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingSupplierId = null;

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingSupplierId) {
            $rules['code'][] = Rule::unique('suppliers', 'code')->ignore($this->editingSupplierId);
        } else {
            $rules['code'][] = Rule::unique('suppliers', 'code');
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['editingSupplierId', 'code', 'name', 'phone', 'email', 'address', 'is_active']);
        $this->is_active = true;

        $count = Supplier::count() + 1;
        $this->code = 'SUP-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        $this->showModal = true;
    }

    public function edit(Supplier $supplier): void
    {
        $this->resetValidation();
        $this->editingSupplierId = $supplier->id;
        $this->code = $supplier->code;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->is_active = $supplier->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function updatedShowModal(bool $value): void
    {
        if (! $value) {
            $this->resetValidation();
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingSupplierId) {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $supplier->update($validated);
            $message = "Data supplier '{$supplier->name}' berhasil diperbarui.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Supplier Diperbarui',
                'text' => $message,
                'toast' => true,
            ]);
        } else {
            $supplier = Supplier::create($validated);
            $message = "Supplier baru '{$supplier->name}' berhasil ditambahkan.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Supplier Ditambahkan',
                'text' => $message,
                'toast' => true,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingSupplierId', 'code', 'name', 'phone', 'email', 'address', 'is_active']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function toggleActive(Supplier $supplier): void
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);
        $statusLabel = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Status supplier '{$supplier->name}' berhasil {$statusLabel}.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Status Supplier Diubah',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function delete(int $id): void
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->stockTransactions()->exists()) {
            $errorMsg = "Supplier '{$supplier->name}' sudah tercatat pada riwayat faktur barang masuk. Gunakan tombol Nonaktifkan agar histori transaksi tetap utuh.";
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $supplierName = $supplier->name;
        $supplier->delete();

        $message = "Supplier '{$supplierName}' berhasil dihapus secara permanen.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Supplier Dihapus',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $suppliers = Supplier::withCount('stockTransactions')
            ->when($this->search, function ($query) {
                $query->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.suppliers.supplier-index', [
            'suppliers' => $suppliers,
        ]);
    }
}
