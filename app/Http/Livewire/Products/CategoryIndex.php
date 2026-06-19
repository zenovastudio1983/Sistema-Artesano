<?php

namespace App\Http\Livewire\Products;

use App\Domains\Products\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Categorías')]
class CategoryIndex extends Component
{
    public string $search = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public ?int $editId = null;

    public string $name = '';
    public string $description = '';
    public ?int $parent_id = null;
    public string $color = '#6366f1';

    public function resetForm(): void
    {
        $this->showForm = false;
        $this->editId = null;
        $this->name = '';
        $this->description = '';
        $this->parent_id = null;
        $this->color = '#6366f1';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editId = $id;
        $this->name = $cat->name;
        $this->description = $cat->description ?? '';
        $this->parent_id = $cat->parent_id;
        $this->color = $cat->color ?? '#6366f1';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'parent_id' => $this->parent_id ?: null,
            'color' => $this->color,
            'is_active' => true,
        ];

        if ($this->editId) {
            Category::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Categoría actualizada.');
        } else {
            Category::create($data);
            session()->flash('success', 'Categoría creada.');
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
        Category::findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('success', 'Categoría eliminada.');
    }

    public function render()
    {
        $categories = Category::with(['parent', 'children' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $parentOptions = Category::whereNull('parent_id')
            ->active()
            ->ordered()
            ->get();

        return view('livewire.products.category-index', compact('categories', 'parentOptions'));
    }
}
