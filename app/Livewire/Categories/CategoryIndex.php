<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Validation\Rule;
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
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingCategoryId) {
            $rules['name'][] = Rule::unique('categories', 'name')->ignore($this->editingCategoryId);
        } else {
            $rules['name'][] = Rule::unique('categories', 'name');
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
        $this->reset(['editingCategoryId', 'name', 'description', 'is_active']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->resetValidation();
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
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

        if ($this->editingCategoryId) {
            $cat = Category::findOrFail($this->editingCategoryId);
            $cat->update($validated);
            $message = "Kategori '{$cat->name}' berhasil diperbarui.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Kategori Diperbarui',
                'text' => $message,
                'toast' => true,
            ]);
        } else {
            $cat = Category::create($validated);
            $message = "Kategori baru '{$cat->name}' berhasil ditambahkan.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Kategori Ditambahkan',
                'text' => $message,
                'toast' => true,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingCategoryId', 'name', 'description', 'is_active']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function toggleActive(Category $category): void
    {
        $category->update(['is_active' => ! $category->is_active]);
        $statusLabel = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Status kategori '{$category->name}' berhasil {$statusLabel}.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Status Kategori Diubah',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            $errorMsg = "Kategori '{$category->name}' masih digunakan oleh data barang. Gunakan tombol Nonaktifkan agar klasifikasi barang tidak rusak.";
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $categoryName = $category->name;
        $category->delete();

        $message = "Kategori '{$categoryName}' berhasil dihapus secara permanen.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Kategori Dihapus',
            'text' => $message,
            'toast' => true,
        ]);
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
