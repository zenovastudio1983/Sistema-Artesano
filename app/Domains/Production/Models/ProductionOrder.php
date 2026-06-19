<?php

namespace App\Domains\Production\Models;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Models\Recipe;
use App\Domains\Users\Models\User;
use App\Support\Enums\ProductionOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductionOrder extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number', 'status', 'product_id', 'recipe_id', 'warehouse_id',
        'planned_quantity', 'produced_quantity', 'rejected_quantity',
        'estimated_material_cost', 'estimated_labor_cost', 'estimated_overhead_cost', 'estimated_total_cost',
        'actual_material_cost', 'actual_labor_cost', 'actual_overhead_cost', 'actual_total_cost', 'unit_cost',
        'planned_start_date', 'planned_end_date', 'started_at', 'finished_at',
        'assigned_to', 'created_by', 'approved_by', 'approved_at',
        'notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionOrderStatus::class,
            'planned_quantity' => 'decimal:4',
            'produced_quantity' => 'decimal:4',
            'rejected_quantity' => 'decimal:4',
            'estimated_material_cost' => 'decimal:4',
            'estimated_labor_cost' => 'decimal:4',
            'estimated_overhead_cost' => 'decimal:4',
            'estimated_total_cost' => 'decimal:4',
            'actual_material_cost' => 'decimal:4',
            'actual_labor_cost' => 'decimal:4',
            'actual_overhead_cost' => 'decimal:4',
            'actual_total_cost' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'approved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'produced_quantity', 'actual_total_cost'])
            ->logOnlyDirty();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionOrderMaterial::class);
    }

    public function logs()
    {
        return $this->hasMany(ProductionOrderLog::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->planned_quantity <= 0) {
            return 0;
        }

        return round(($this->produced_quantity / $this->planned_quantity) * 100, 1);
    }

    public function getCostVarianceAttribute(): float
    {
        return $this->actual_total_cost - $this->estimated_total_cost;
    }

    public function canTransitionTo(ProductionOrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ProductionOrderStatus::Planned->value,
            ProductionOrderStatus::InProgress->value,
        ]);
    }

    public function scopeByStatus($query, ProductionOrderStatus $status)
    {
        return $query->where('status', $status);
    }
}
