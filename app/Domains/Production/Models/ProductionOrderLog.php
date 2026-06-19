<?php

namespace App\Domains\Production\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderLog extends Model
{
    protected $fillable = [
        'production_order_id', 'from_status', 'to_status', 'notes', 'created_by',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
