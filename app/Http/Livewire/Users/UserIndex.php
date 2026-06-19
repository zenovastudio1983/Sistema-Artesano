<?php

namespace App\Http\Livewire\Users;

use App\Domains\Users\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Usuarios')]
class UserIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterRole = '';

    public string $sortBy = 'name';
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function updatedSearch(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        $this->sortDir = ($this->sortBy === $field && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortBy = $field;
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return;
        }
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        User::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Usuario eliminado.');
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn($q) => $q->role($this->filterRole))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        return view('livewire.users.user-index', compact('users', 'roles'));
    }
}
