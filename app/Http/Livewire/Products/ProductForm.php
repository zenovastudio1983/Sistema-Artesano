<?php

namespace App\Http\Livewire\Products;

use App\Domains\Products\Models\Category;
use App\Domains\Products\Models\Product;
use App\Support\Enums\ProductStatus;
use App\Support\Enums\ProductType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
#[Layout('layouts.app')]
#[Title('Producto')]
class ProductForm extends Component
{
    public ?int $productId = null;

    // Imagen de tienda (URL externa)
    public string $storefrontImageUrl = '';

    // Identificación
    public string $sku = '';
    public string $barcode = '';
    public string $name = '';
    public string $description = '';
    public string $type = 'finished_product';
    public int $categoryId = 0;
    public string $unit = '';
    public string $secondaryUnit = '';
    public string $conversionFactor = '';
    public string $status = 'active';

    // Costos y precios
    public string $cost = '';
    public string $standardCost = '';
    public string $price = '';
    public string $minPrice = '';
    public string $marginPercent = '';

    // Control de stock
    public string $stockMinimum = '';
    public string $stockMaximum = '';
    public string $reorderPoint = '';

    // Flags de uso
    public bool $is_purchasable = true;
    public bool $is_sellable = true;
    public bool $is_producible = false;
    public bool $track_batches = false;
    public bool $track_expiry = false;
    public string $shelfLifeDays = '';
    public string $manufacturingDate = '';
    public string $expiryDate = '';

    // Físico
    public string $weight = '';
    public string $weightUnit = 'kg';
    public string $volume = '';
    public string $volumeUnit = 'L';

    public string $notes = '';

    // Tienda pública
    public bool $is_published = false;
    public string $publicSlug = '';
    public string $publicDescription = '';
    public bool $is_made_to_order = false;
    public string $leadTimeDays = '';

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->productId       = $product->id;
            $this->sku             = $product->sku ?? '';
            $this->barcode         = $product->barcode ?? '';
            $this->name            = $product->name;
            $this->description     = $product->description ?? '';
            $this->type            = $product->type instanceof ProductType ? $product->type->value : ($product->type ?? 'finished_product');
            $this->categoryId      = $product->category_id ?? 0;
            $this->unit            = $product->unit ?? '';
            $this->secondaryUnit   = $product->secondary_unit ?? '';
            $this->conversionFactor = (string) ($product->conversion_factor ?? '');
            $this->status          = $product->status instanceof ProductStatus ? $product->status->value : ($product->status ?? 'active');
            $this->cost            = (string) ($product->cost ?? '');
            $this->standardCost    = (string) ($product->standard_cost ?? '');
            $this->price           = (string) ($product->price ?? '');
            $this->minPrice        = (string) ($product->min_price ?? '');
            $this->marginPercent   = (string) ($product->margin_percent ?? '');
            $this->stockMinimum    = (string) ($product->stock_minimum ?? '');
            $this->stockMaximum    = (string) ($product->stock_maximum ?? '');
            $this->reorderPoint    = (string) ($product->reorder_point ?? '');
            $this->is_purchasable  = (bool) $product->is_purchasable;
            $this->is_sellable     = (bool) $product->is_sellable;
            $this->is_producible   = (bool) $product->is_producible;
            $this->track_batches   = (bool) $product->track_batches;
            $this->track_expiry    = (bool) $product->track_expiry;
            $this->shelfLifeDays   = (string) ($product->shelf_life_days ?? '');
            $this->manufacturingDate = $product->manufacturing_date?->format('Y-m-d') ?? '';
            $this->expiryDate      = $product->expiry_date?->format('Y-m-d') ?? '';
            $this->weight          = (string) ($product->weight ?? '');
            $this->weightUnit      = $product->weight_unit ?? 'kg';
            $this->volume          = (string) ($product->volume ?? '');
            $this->volumeUnit      = $product->volume_unit ?? 'L';
            $this->notes                = $product->notes ?? '';
            $this->is_published         = (bool) $product->is_published;
            $this->publicSlug           = $product->public_slug ?? '';
            $this->publicDescription    = $product->public_description ?? '';
            $this->is_made_to_order     = (bool) $product->is_made_to_order;
            $this->leadTimeDays         = (string) ($product->lead_time_days ?? '');
            $this->storefrontImageUrl   = $product->storefront_image_url ?? '';
        }
    }

    public function updatedName(): void
    {
        if (! $this->publicSlug && $this->name) {
            $this->publicSlug = \Illuminate\Support\Str::slug($this->name);
        }
    }

    public function updatedType(string $value): void
    {
        match($value) {
            'raw_material', 'packaging', 'supply' => [
                $this->is_purchasable = true,
                $this->is_sellable    = false,
                $this->is_producible  = false,
            ],
            'finished_product', 'semi_finished' => [
                $this->is_producible = true,
                $this->is_sellable   = true,
            ],
            default => null,
        };
    }

    public function updatedManufacturingDate(): void
    {
        $this->recalcShelfLife();
    }

    public function updatedExpiryDate(): void
    {
        $this->recalcShelfLife();
    }

    private function recalcShelfLife(): void
    {
        if ($this->manufacturingDate && $this->expiryDate) {
            $start = \Carbon\Carbon::parse($this->manufacturingDate);
            $end   = \Carbon\Carbon::parse($this->expiryDate);
            $this->shelfLifeDays = $end >= $start ? (string) $start->diffInDays($end) : '';
        } else {
            $this->shelfLifeDays = '';
        }
    }

    public function updatedCost(): void
    {
        if ($this->cost && $this->price) {
            $cost  = (float) $this->cost;
            $price = (float) $this->price;
            if ($price > 0) {
                $this->marginPercent = (string) round(($price - $cost) / $price * 100, 2);
            }
        }
    }

    public function updatedPrice(): void
    {
        $this->updatedCost();
    }

    public function save(): void
    {
        $this->validate([
            'name'   => 'required|string|max:200',
            'type'   => 'required|in:raw_material,finished_product,semi_finished,packaging,supply',
            'status' => 'required|in:active,inactive,discontinued',
            'sku'    => [
                'nullable', 'string', 'max:100',
                $this->productId
                    ? \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($this->productId)
                    : \Illuminate\Validation\Rule::unique('products', 'sku'),
            ],
            'cost'               => 'nullable|numeric|min:0',
            'price'              => 'nullable|numeric|min:0',
            'manufacturingDate'      => 'nullable|date',
            'expiryDate'            => 'nullable|date|after_or_equal:manufacturingDate',
            'storefrontImageUrl'    => 'nullable|url|max:2000',
            'publicSlug'            => [
                'nullable', 'string', 'max:120', 'regex:/^[a-z0-9\-]+$/',
                $this->productId
                    ? \Illuminate\Validation\Rule::unique('products', 'public_slug')->ignore($this->productId)
                    : \Illuminate\Validation\Rule::unique('products', 'public_slug'),
            ],
            'leadTimeDays'       => 'nullable|integer|min:1|max:365',
        ]);

        $data = [
            'sku'               => $this->sku ?: null,
            'barcode'           => $this->barcode ?: null,
            'name'              => $this->name,
            'description'       => $this->description ?: null,
            'type'              => $this->type,
            'category_id'       => $this->categoryId ?: null,
            'unit'              => $this->unit ?: null,
            'secondary_unit'    => $this->secondaryUnit ?: null,
            'conversion_factor' => $this->conversionFactor !== '' ? (float) $this->conversionFactor : 1,
            'status'            => $this->status,
            'cost'              => $this->cost !== '' ? (float) $this->cost : 0,
            'standard_cost'     => $this->standardCost !== '' ? (float) $this->standardCost : 0,
            'price'             => $this->price !== '' ? (float) $this->price : 0,
            'min_price'         => $this->minPrice !== '' ? (float) $this->minPrice : 0,
            'margin_percent'    => $this->marginPercent !== '' ? (float) $this->marginPercent : 0,
            'stock_minimum'     => $this->stockMinimum !== '' ? (float) $this->stockMinimum : 0,
            'stock_maximum'     => $this->stockMaximum !== '' ? (float) $this->stockMaximum : null,
            'reorder_point'     => $this->reorderPoint !== '' ? (float) $this->reorderPoint : 0,
            'is_purchasable'    => $this->is_purchasable,
            'is_sellable'       => $this->is_sellable,
            'is_producible'     => $this->is_producible,
            'track_batches'     => $this->track_batches,
            'track_expiry'      => $this->track_expiry,
            'shelf_life_days'      => $this->shelfLifeDays !== '' ? (int) $this->shelfLifeDays : null,
            'manufacturing_date'   => $this->manufacturingDate ?: null,
            'expiry_date'          => $this->expiryDate ?: null,
            'weight'            => $this->weight !== '' ? (float) $this->weight : null,
            'weight_unit'       => $this->weightUnit ?: null,
            'volume'            => $this->volume !== '' ? (float) $this->volume : null,
            'volume_unit'       => $this->volumeUnit ?: null,
            'notes'             => $this->notes ?: null,
            'is_published'      => $this->is_published,
            'public_slug'       => $this->publicSlug ?: null,
            'public_description'=> $this->publicDescription ?: null,
            'is_made_to_order'      => $this->is_made_to_order,
            'lead_time_days'        => $this->leadTimeDays !== '' ? (int) $this->leadTimeDays : null,
            'storefront_image_url'  => $this->storefrontImageUrl ?: null,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            $message = 'Producto actualizado.';
        } else {
            $product = Product::create($data);
            $message = 'Producto creado.';
        }

        $id = $product->id;

        session()->flash('success', $message);
        $this->redirect(route('products.show', $id));
    }

    public function render()
    {
        return view('livewire.products.product-form', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'types'      => ProductType::cases(),
            'statuses'   => ProductStatus::cases(),
        ]);
    }
}
