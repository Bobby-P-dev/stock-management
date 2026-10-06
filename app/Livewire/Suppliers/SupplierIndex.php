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

    public function create(): void
    {
        $this->reset(['editingSupplierId', 'code', 'name', 'phone', 'email', 'address']);
        $this->is_active = true;

        $count = Supplier::count() + 1;
        $this->code = 'SUP-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        $this->showModal = true;
    }

    public function edit(Supplier $supplier): void
    {
        $this->editingSupplierId = $supplier->id;
        $this->code = $supplier->code;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->is_active = $supplier->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingSupplierId) {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $supplier->update($validated);
            session()->flash('success', "Data supplier '{$supplier->name}' berhasil diperbarui.");
        } else {
            $supplier = Supplier::create($validated);
            session()->flash('success', "Supplier baru '{$supplier->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['editingSupplierId', 'code', 'name', 'phone', 'email', 'address']);
    }

    public function toggleActive(Supplier $supplier): void
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);
        session()->flash('success', "Status supplier '{$supplier->name}' berhasil diubah.");
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
