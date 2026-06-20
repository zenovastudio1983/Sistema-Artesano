<?php

namespace App\Http\Livewire\Sales;

use App\Domains\Products\Models\Product;
use App\Domains\Sales\Models\Customer;
use App\Domains\Sales\Models\Sale;
use App\Domains\Sales\Models\SaleItem;
use App\Domains\Inventory\Models\Warehouse;
use App\Support\Enums\SaleStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Venta')]
class SaleForm extends Component
{
    public ?int $saleId = null;

    public int $customerId = 0;
    public int $warehouseId = 0;
    public string $saleDate = '';
    public string $dueDate = '';
    public string $reference = '';
    public string $notes = '';
    public string $currency = 'ARS';
    public string $taxRate = '21';
    public string $discountPercent = '0';

    public array $items = [];

    public float $subtotal = 0;
    public float $discountAmount = 0;
    public float $taxAmount = 0;
    public float $total = 0;

    public function mount(?Sale $sale = null): void
    {
        $this->saleDate = now()->toDateString();

        if ($sale && $sale->exists) {
            $this->saleId          = $sale->id;
            $this->customerId      = $sale->customer_id ?? 0;
            $this->warehouseId     = $sale->warehouse_id ?? 0;
            $this->saleDate        = $sale->sale_date->toDateString();
            $this->dueDate         = $sale->due_date?->toDateString() ?? '';
            $this->reference       = $sale->reference ?? '';
            $this->notes           = $sale->notes ?? '';
            $this->currency        = $sale->currency ?? 'ARS';
            $this->taxRate         = (string) $sale->tax_rate;
            $this->discountPercent = (string) $sale->discount_percent;

            foreach ($sale->items->load('product') as $item) {
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
                $this->items[$index]['unit_price'] = (float) ($product->price ?? 0);
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

    public function updatedDiscountPercent(): void { $this->recalculate(); }
    public function updatedTaxRate(): void { $this->recalculate(); }

    private function recalculate(): void
    {
        $this->subtotal       = collect($this->items)->sum('subtotal');
        $this->discountAmount = round($this->subtotal * ((float) $this->discountPercent / 100), 4);
        $net                  = $this->subtotal - $this->discountAmount;
        $this->taxAmount      = round($net * ((float) $this->taxRate / 100), 4);
        $this->total          = $net + $this->taxAmount;
    }

    public function save(): void
    {
        $this->validate([
            'saleDate'    => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->recalculate();

        $data = [
            'customer_id'      => $this->customerId ?: null,
            'warehouse_id'     => $this->warehouseId ?: null,
            'sale_date'        => $this->saleDate,
            'due_date'         => $this->dueDate ?: null,
            'reference'        => $this->reference ?: null,
            'notes'            => $this->notes ?: null,
            'currency'         => $this->currency,
            'tax_rate'         => (float) $this->taxRate,
            'discount_percent' => (float) $this->discountPercent,
            'subtotal'         => $this->subtotal,
            'discount_amount'  => $this->discountAmount,
            'tax_amount'       => $this->taxAmount,
            'total'            => $this->total,
        ];

        if ($this->saleId) {
            $sale = Sale::findOrFail($this->saleId);
            $sale->update($data);
            $sale->items()->delete();
            $message = 'Venta actualizada.';
        } else {
            $data['status']     = SaleStatus::Quotation;
            $data['created_by'] = auth()->id();
            $sale = Sale::create($data);
            $message = 'Venta creada.';
        }

        foreach ($this->items as $item) {
            $q = (float) $item['quantity'];
            $p = (float) $item['unit_price'];
            $d = (float) ($item['discount_percent'] ?? 0);
            $subtotal = round($q * $p * (1 - $d / 100), 4);

            SaleItem::create([
                'sale_id'          => $sale->id,
                'product_id'       => $item['product_id'],
                'description'      => $item['description'] ?? null,
                'quantity'         => $q,
                'unit_price'       => $p,
                'discount_percent' => $d,
                'subtotal'         => $subtotal,
            ]);
        }

        session()->flash('success', $message);
        $this->redirect(route('sales.show', $sale));
    }

    public function render()
    {
        return view('livewire.sales.sale-form', [
            'customers'  => Customer::active()->orderBy('business_name')->get(),
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'products'   => Product::where('status', 'active')->where('is_sellable', true)->orderBy('name')->get(),
        ]);
    }
}
