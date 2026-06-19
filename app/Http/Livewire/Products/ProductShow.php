<?php

namespace App\Http\Livewire\Products;

use App\Domains\Products\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class ProductShow extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'category',
            'inventory.warehouse',
            'recipes' => fn($q) => $q->where('is_active', true)->orderBy('is_default', 'desc'),
        ]);
    }

    public function render()
    {
        return view('livewire.products.product-show');
    }
}
