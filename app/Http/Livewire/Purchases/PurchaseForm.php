<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Products\Models\Product;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Models\PurchaseOrderItem;
use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Purchases\Models\Supplier;
use App\Support\Enums\PurchaseOrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Compra')]
class PurchaseForm extends Component
{
    public ?int $orderId = null;

    public int $supplierId = 0;
    public int $warehouseId = 0;
    public string $orderDate = '';
    public string $expectedDate = '';
    public string $reference = '';
    public string $notes = '';
    public string $currency = 'PEN';
    public string $taxRate = '18';
    public string $shippingCost = '0';

    public array $items = [];

    public float $subtotal = 0;
    public float $taxAmount = 0;
    public float $total = 0;

    public function mount(?PurchaseOrder $order = null): void
    {
        $this->orderDate = now()->toDateString();

        if ($order && $order->exists) {
            $this->orderId = $order->id;
            $this->supplierId = $order->supplier_id;
            $this->warehouseId = $order->warehouse_id ?? 0;
            $this->orderDate = $order->order_date->toDateString();
            $this->expectedDate = $order->expected_date?->toDateString() ?? '';
            $this->reference = $order->reference ?? '';
            $this->notes = $order->notes ?? '';
            $this->currency = $order->currency ?? 'PEN';
            $this->taxRate = (string) $order->tax_rate;
            $this->shippingCost = (string) $order->shipping_cost;

            foreach ($order->items->load('product') as $item) {
                $this->items[] = [
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product->name ?? '',
                    'description'      => $item->description ?? '',
                    'quantity'         => (float) $item->quantity,
                    'unit_price'       => (float) $item->unit_price,
                    'discount_percent' => (float) $item->discount_percent,
                    'subtotal'         => (float) $item->subtotal,
                ];
            }
        }

        if (empty($this->items)) {
            $this->addItem();
        }

        $this->recalculate();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => 0, 'product_name' => '', 'description' => '',
            'quantity' => 1, 'unit_price' => 0, 'discount_percent' => 0, 'subtotal' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculate();
    }

    public function updatedItems(mixed $value, string $key): void
    {
        [$index, $field] = explode('.', $key, 2);

        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['unit_price'] = (float) ($product->cost ?? 0);
            }
        }

        if (in_array($field, ['quantity', 'unit_price', 'discount_percent'])) {
            $q = (float) ($this->items[$index]['quantity'] ?? 0);
            $p = (float) ($this->items[$index]['unit_price'] ?? 0);
            $d = (float) ($this->items[$index]['discount_percent'] ?? 0);
            $this->items[$index]['subtotal'] = round($q * $p * (1 - $d / 100), 4);
        }

        $this->recalculate();
    }

    private function recalculate(): void
    {
        $this->subtotal = collect($this->items)->sum('subtotal');
        $this->taxAmount = round($this->subtotal * ((float) $this->taxRate / 100), 4);
        $this->total = $this->subtotal + $this->taxAmount + (float) $this->shippingCost;
    }

    public function save(): void
    {
        $this->validate([
            'supplierId'  => 'required|exists:suppliers,id',
            'warehouseId' => 'required|exists:warehouses,id',
            'orderDate'   => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->recalculate();

        $data = [
            'supplier_id'     => $this->supplierId,
            'warehouse_id'    => $this->warehouseId,
            'order_date'      => $this->orderDate,
            'expected_date'   => $this->expectedDate ?: null,
            'reference'       => $this->reference ?: null,
            'notes'           => $this->notes ?: null,
            'currency'        => $this->currency,
            'tax_rate'        => (float) $this->taxRate,
            'shipping_cost'   => (float) $this->shippingCost,
            'subtotal'        => $this->subtotal,
            'tax_amount'      => $this->taxAmount,
            'total'           => $this->total,
        ];

        if ($this->orderId) {
            $order = PurchaseOrder::findOrFail($this->orderId);
            $order->update($data);
            $order->items()->delete();
            $message = 'Orden actualizada.';
        } else {
            $data['status']     = PurchaseOrderStatus::Draft;
            $data['created_by'] = auth()->id();
            $order = PurchaseOrder::create($data);
            $message = 'Orden creada.';
        }

        foreach ($this->items as $item) {
            $q = (float) $item['quantity'];
            $p = (float) $item['unit_price'];
            $d = (float) ($item['discount_percent'] ?? 0);
            $subtotal = round($q * $p * (1 - $d / 100), 4);

            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $item['product_id'],
                'description'       => $item['description'] ?? null,
                'quantity'          => $q,
                'unit_price'        => $p,
                'discount_percent'  => $d,
                'subtotal'          => $subtotal,
            ]);
        }

        session()->flash('success', $message);
        $this->redirect(route('purchases.show', $order));
    }

    public function render()
    {
        return view('livewire.purchases.purchase-form', [
            'suppliers'  => Supplier::active()->orderBy('business_name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'products'   => Product::where('status', 'active')->where('is_purchasable', true)->orderBy('name')->get(),
        ]);
    }
}
