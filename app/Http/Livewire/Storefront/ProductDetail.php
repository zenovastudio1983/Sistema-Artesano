<?php

namespace App\Http\Livewire\Storefront;

use App\Domains\Products\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ProductDetail extends Component
{
    public Product $product;

    public function mount(string $slug): void
    {
        $this->product = Product::query()
            ->where('public_slug', $slug)
            ->where('is_published', true)
            ->with(['media', 'inventory'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.storefront.product-detail')
            ->title($this->product->name . ' — ' . config('erp.company.name'));
    }
}
