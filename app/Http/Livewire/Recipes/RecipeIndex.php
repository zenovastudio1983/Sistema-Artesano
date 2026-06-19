<?php

namespace App\Http\Livewire\Recipes;

use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Models\Recipe;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Recetas')]
class RecipeIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    public string $sortBy = 'name';
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        $this->sortDir = ($this->sortBy === $field && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortBy = $field;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Recipe::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Receta eliminada.');
    }

    public function render()
    {
        $recipes = Recipe::with(['product.category'])
            ->withCount('ingredients')
            ->when($this->search, fn($q) => $q->where(function ($inner) {
                $inner->where('name', 'ilike', "%{$this->search}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->filterStatus === 'active', fn($q) => $q->active())
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(20);

        return view('livewire.recipes.recipe-index', compact('recipes'));
    }
}
