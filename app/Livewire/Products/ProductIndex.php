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

    public string $name = '';

    public ?int $category_id = null;

    public ?int $unit_id = null;

    public int $minimum_stock = 0;

    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'sku' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingProductId) {
            $rules['sku'][] = Rule::unique('products', 'sku')->ignore($this->editingProductId);
        } else {
            $rules['sku'][] = Rule::unique('products', 'sku');
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
        $this->reset(['editingProductId', 'sku', 'name', 'category_id', 'unit_id', 'minimum_stock', 'is_active']);
        $this->is_active = true;
        $this->minimum_stock = 5;
        $this->showModal = true;
    }

    public function edit(Product $product): void
    {
        $this->editingProductId = $product->id;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
        $this->minimum_stock = $product->minimum_stock;
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update($validated);
            session()->flash('success', "Barang '{$product->name}' berhasil diperbarui.");
        } else {
            $validated['current_stock'] = 0;
            $product = Product::create($validated);
            session()->flash('success', "Barang baru '{$product->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['editingProductId', 'sku', 'name', 'category_id', 'unit_id', 'minimum_stock']);
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
        session()->flash('success', "Status barang '{$product->name}' berhasil diubah.");
    }

    public function render()
    {
        $categories = Category::active()->get();
        $units = Unit::active()->get();

        $products = Product::with(['category', 'unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('sku', 'like', '%'.$this->search.'%')
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
