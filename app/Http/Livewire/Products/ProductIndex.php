<?php

namespace App\Http\Livewire\Products;

use App\Domains\Products\Models\Category;
use App\Domains\Products\Models\Product;
use App\Support\Enums\ProductStatus;
use App\Support\Enums\ProductType;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Productos')]
class ProductIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterType = '';

    #[Url(except: '')]
    public string $filterStatus = '';

    #[Url(except: 0)]
    public int $filterCategory = 0;

    #[Url(except: 'name')]
    public string $sortBy = 'name';

    #[Url(except: 'asc')]
    public string $sortDir = 'asc';

    public int $perPage = 25;

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->authorize('delete products');

        $product = Product::findOrFail($this->deletingId);

        if ($product->inventory()->where('quantity', '>', 0)->exists()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar un producto con stock disponible.',
            ]);
            $this->showDeleteModal = false;
            return;
        }

        $product->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    public function render()
    {
        $products = Product::with(['category.parent', 'inventory'])
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filterType, fn($q) => $q->byType($this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => Category::with('parent')->active()->ordered()->get(),
            'types' => ProductType::cases(),
            'statuses' => ProductStatus::cases(),
        ]);
    }
}
