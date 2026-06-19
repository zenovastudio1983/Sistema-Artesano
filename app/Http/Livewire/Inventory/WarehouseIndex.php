<?php

namespace App\Http\Livewire\Inventory;

use App\Domains\Inventory\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Almacenes')]
class WarehouseIndex extends Component
{
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public ?int $editId = null;

    public string $name = '';
    public string $code = '';
    public string $address = '';
    public bool $is_active = true;
    public bool $is_default = false;

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editId = null;
        $this->name = '';
        $this->code = '';
        $this->address = '';
        $this->is_active = true;
        $this->is_default = false;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $warehouse = Warehouse::findOrFail($id);
        $this->editId = $id;
        $this->name = $warehouse->name;
        $this->code = $warehouse->code ?? '';
        $this->address = $warehouse->address ?? '';
        $this->is_active = $warehouse->is_active;
        $this->is_default = $warehouse->is_default ?? false;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'address' => $this->address ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editId) {
            Warehouse::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Almacén actualizado.');
        } else {
            Warehouse::create($data);
            session()->flash('success', 'Almacén creado.');
        }

        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Warehouse::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Almacén eliminado.');
    }

    public function render()
    {
        $warehouses = Warehouse::withCount(['inventory'])
            ->orderBy('name')
            ->get();

        return view('livewire.inventory.warehouse-index', compact('warehouses'));
    }
}
