<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Data Barang - Berkah Mandiri Inventory')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedCategory = '';

    public string $stockStatusFilter = '';

    public bool $showModal = false;

    public ?int $editingProductId = null;

    public string $sku = '';

    public string $barcode = '';

    public string $name = '';

    public ?int $category_id = null;

    public ?int $unit_id = null;

    public int $minimum_stock = 0;

    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'sku' => ['required', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingProductId) {
            $rules['sku'][] = Rule::unique('products', 'sku')->ignore($this->editingProductId);
            $rules['barcode'][] = Rule::unique('products', 'barcode')->ignore($this->editingProductId);
        } else {
            $rules['sku'][] = Rule::unique('products', 'sku');
            $rules['barcode'][] = Rule::unique('products', 'barcode');
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStockStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['editingProductId', 'sku', 'barcode', 'name', 'category_id', 'unit_id', 'minimum_stock', 'is_active']);
        $this->is_active = true;
        $this->minimum_stock = 5;
        $this->showModal = true;
    }

    public function edit(Product $product): void
    {
        $this->resetValidation();
        $this->editingProductId = $product->id;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode ?? '';
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
        $this->minimum_stock = $product->minimum_stock;
        $this->is_active = $product->is_active;
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
        $validated['barcode'] = ! empty($validated['barcode']) ? trim($validated['barcode']) : null;

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update($validated);
            $message = "Barang '{$product->name}' berhasil diperbarui.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Data Barang Diperbarui',
                'text' => $message,
                'toast' => true,
            ]);
        } else {
            $validated['current_stock'] = 0;
            $product = Product::create($validated);
            $message = "Barang baru '{$product->name}' berhasil ditambahkan.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Barang Baru Ditambahkan',
                'text' => $message,
                'toast' => true,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingProductId', 'sku', 'barcode', 'name', 'category_id', 'unit_id', 'minimum_stock', 'is_active']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
        $statusLabel = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Status barang '{$product->name}' berhasil {$statusLabel}.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Status Barang Diubah',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);

        if ($product->transactionItems()->exists() || $product->opnameItems()->exists()) {
            $errorMsg = "Barang '{$product->name}' sudah memiliki riwayat mutasi atau stock opname. Gunakan tombol Nonaktifkan agar histori audit tetap terjaga.";
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $productName = $product->name;
        $product->delete();

        $message = "Barang '{$productName}' berhasil dihapus secara permanen.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Barang Dihapus',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $categories = Category::active()->get();
        $units = Unit::active()->get();

        $products = Product::with(['category', 'unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('barcode', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->when($this->stockStatusFilter === 'low', fn ($q) => $q->lowStock()->where('current_stock', '>', 0))
            ->when($this->stockStatusFilter === 'out', fn ($q) => $q->where('current_stock', 0))
            ->when($this->stockStatusFilter === 'safe', fn ($q) => $q->whereColumn('current_stock', '>', 'minimum_stock'))
            ->latest('id')
            ->paginate(10);

        return view('livewire.products.product-index', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
        ]);
    }
}
