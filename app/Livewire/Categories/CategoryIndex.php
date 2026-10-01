<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Data Kategori - Berkah Mandiri Inventory')]
class CategoryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$this->editingCategoryId],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->reset(['editingCategoryId', 'name', 'description']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingCategoryId) {
            $cat = Category::findOrFail($this->editingCategoryId);
            $cat->update($validated);
            session()->flash('success', "Kategori '{$cat->name}' berhasil diperbarui.");
        } else {
            $cat = Category::create($validated);
            session()->flash('success', "Kategori baru '{$cat->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['editingCategoryId', 'name', 'description']);
    }

    public function toggleActive(Category $category): void
    {
        $category->update(['is_active' => ! $category->is_active]);
        session()->flash('success', "Status kategori '{$category->name}' berhasil diubah.");
    }

    public function render()
    {
        $categories = Category::withCount('products')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest('id')
            ->paginate(10);

        return view('livewire.categories.category-index', [
            'categories' => $categories,
        ]);
    }
}
