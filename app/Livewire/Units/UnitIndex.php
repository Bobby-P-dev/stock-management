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

    public function create(): void
    {
        $this->reset(['editingUnitId', 'name', 'symbol']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Unit $unit): void
    {
        $this->editingUnitId = $unit->id;
        $this->name = $unit->name;
        $this->symbol = $unit->symbol;
        $this->is_active = $unit->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingUnitId) {
            $unit = Unit::findOrFail($this->editingUnitId);
            $unit->update($validated);
            session()->flash('success', "Satuan '{$unit->name}' berhasil diperbarui.");
        } else {
            $unit = Unit::create($validated);
            session()->flash('success', "Satuan baru '{$unit->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['editingUnitId', 'name', 'symbol']);
    }

    public function toggleActive(Unit $unit): void
    {
        $unit->update(['is_active' => ! $unit->is_active]);
        session()->flash('success', "Status satuan '{$unit->name}' berhasil diubah.");
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
