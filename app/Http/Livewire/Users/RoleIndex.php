<?php

namespace App\Http\Livewire\Users;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Roles y Permisos')]
class RoleIndex extends Component
{
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public ?int $editId = null;

    public string $name = '';
    public array $selectedPermissions = [];

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editId = null;
        $this->name = '';
        $this->selectedPermissions = [];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->editId = $id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100|unique:roles,name' . ($this->editId ? ",{$this->editId}" : ''),
        ]);

        if ($this->editId) {
            $role = Role::findOrFail($this->editId);
            $role->update(['name' => $this->name]);
            session()->flash('success', 'Rol actualizado.');
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            session()->flash('success', 'Rol creado.');
        }

        $role->syncPermissions($this->selectedPermissions);
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Role::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Rol eliminado.');
    }

    public function render()
    {
        return view('livewire.users.role-index', [
            'roles'       => Role::with('permissions')->withCount('users')->get(),
            'permissions' => Permission::orderBy('name')->get()->groupBy(fn($p) => explode(' ', $p->name)[1] ?? 'other'),
        ]);
    }
}
