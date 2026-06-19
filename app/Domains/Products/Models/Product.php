<?php

namespace App\Domains\Products\Models;

use App\Support\Enums\ProductStatus;
use App\Support\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'sku', 'barcode', 'name', 'description', 'type', 'category_id', 'unit',
        'secondary_unit', 'conversion_factor', 'cost', 'standard_cost',
        'last_purchase_cost', 'average_cost', 'price', 'min_price', 'margin_percent',
        'stock_minimum', 'stock_maximum', 'reorder_point', 'track_batches',
        'track_expiry', 'shelf_life_days', 'weight', 'weight_unit', 'volume',
        'volume_unit', 'status', 'is_purchasable', 'is_sellable', 'is_producible',
        'meta', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'status' => ProductStatus::class,
            'cost' => 'decimal:4',
            'standard_cost' => 'decimal:4',
            'last_purchase_cost' => 'decimal:4',
            'average_cost' => 'decimal:4',
            'price' => 'decimal:4',
            'min_price' => 'decimal:4',
            'margin_percent' => 'decimal:2',
            'stock_minimum' => 'decimal:4',
            'stock_maximum' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'track_batches' => 'boolean',
            'track_expiry' => 'boolean',
            'is_purchasable' => 'boolean',
            'is_sellable' => 'boolean',
            'is_producible' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'cost', 'price', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('main_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory()
    {
        return $this->hasMany(\App\Domains\Inventory\Models\Inventory::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(\App\Domains\Inventory\Models\StockMovement::class);
    }

    public function recipes()
    {
        return $this->hasMany(\App\Domains\Recipes\Models\Recipe::class);
    }

    public function defaultRecipe()
    {
        return $this->hasOne(\App\Domains\Recipes\Models\Recipe::class)
            ->where('is_default', true)
            ->where('is_active', true);
    }

    public function recipeIngredients()
    {
        return $this->hasMany(\App\Domains\Recipes\Models\RecipeIngredient::class);
    }

    public function getTotalStockAttribute(): float
    {
        return $this->inventory->sum('quantity');
    }

    public function getAvailableStockAttribute(): float
    {
        return $this->inventory->sum('available_quantity');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->stock_minimum && $this->stock_minimum > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->total_stock <= 0;
    }

    public function getMarginAttribute(): float
    {
        if ($this->price <= 0) {
            return 0;
        }

        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }

    public function getImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('main_image') ?? $this->getFirstMedia('images');

        return $media?->getUrl();
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw(
            'EXISTS (SELECT 1 FROM inventory i WHERE i.product_id = products.id AND i.quantity <= products.stock_minimum AND products.stock_minimum > 0)'
        );
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
                ->orWhere('sku', 'ilike', "%{$term}%")
                ->orWhere('barcode', 'ilike', "%{$term}%");
        });
    }
}
