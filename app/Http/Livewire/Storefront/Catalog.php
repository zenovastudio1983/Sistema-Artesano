<?php

namespace App\Http\Livewire\Storefront;

use App\Domains\Products\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Catálogo')]
class Catalog extends Component
{
    public string $search = '';

    public function render()
    {
        $products = Product::query()
            ->where('is_published', true)
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->with(['media', 'inventory'])
            ->orderBy('name')
            ->get();

        return view('livewire.storefront.catalog', compact('products'));
    }
}
