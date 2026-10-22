<?php

namespace App\Livewire\Units;

use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Data Satuan - Berkah Mandiri Inventory')]
class UnitIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingUnitId = null;

    public string $name = '';

    public string $symbol = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:15'],
            'is_active' => ['boolean'],
        ];

        if ($this->editingUnitId) {
            $rules['name'][] = Rule::unique('units', 'name')->ignore($this->editingUnitId);
            $rules['symbol'][] = Rule::unique('units', 'symbol')->ignore($this->editingUnitId);
        } else {
            $rules['name'][] = Rule::unique('units', 'name');
            $rules['symbol'][] = Rule::unique('units', 'symbol');
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
        $this->reset(['editingUnitId', 'name', 'symbol', 'is_active']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Unit $unit): void
    {
        $this->resetValidation();
        $this->editingUnitId = $unit->id;
        $this->name = $unit->name;
        $this->symbol = $unit->symbol;
        $this->is_active = $unit->is_active;
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

        if ($this->editingUnitId) {
            $unit = Unit::findOrFail($this->editingUnitId);
            $unit->update($validated);
            $message = "Satuan '{$unit->name}' berhasil diperbarui.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Satuan Diperbarui',
                'text' => $message,
                'toast' => true,
            ]);
        } else {
            $unit = Unit::create($validated);
            $message = "Satuan baru '{$unit->name}' berhasil ditambahkan.";
            session()->flash('success', $message);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Satuan Ditambahkan',
                'text' => $message,
                'toast' => true,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingUnitId', 'name', 'symbol', 'is_active']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function toggleActive(Unit $unit): void
    {
        $unit->update(['is_active' => ! $unit->is_active]);
        $statusLabel = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Status satuan '{$unit->name}' berhasil {$statusLabel}.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Status Satuan Diubah',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function delete(int $id): void
    {
        $unit = Unit::findOrFail($id);

        if ($unit->products()->exists()) {
            $errorMsg = "Satuan '{$unit->name}' ({$unit->symbol}) masih digunakan oleh data barang. Gunakan tombol Nonaktifkan agar satuan barang tidak terputus.";
            session()->flash('error', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => $errorMsg,
                'toast' => false,
            ]);

            return;
        }

        $unitName = $unit->name;
        $unit->delete();

        $message = "Satuan '{$unitName}' berhasil dihapus secara permanen.";
        session()->flash('success', $message);
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Satuan Dihapus',
            'text' => $message,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $units = Unit::withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('symbol', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.units.unit-index', [
            'units' => $units,
        ]);
    }
}
