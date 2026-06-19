<?php

namespace App\Domains\Sales\Services;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Products\Models\Product;
use App\Domains\Sales\Models\Sale;
use App\Domains\Sales\Models\SaleItem;
use App\Support\Enums\MovementType;
use App\Support\Enums\SaleStatus;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(private StockService $stockService) {}

    public function create(array $data, array $items): Sale
    {
        return DB::transaction(function () use ($data, $items) {
            $sale = Sale::create([
                ...$data,
                'order_number' => $this->generateOrderNumber($data['type'] ?? 'sale'),
                'status' => SaleStatus::Quotation,
                'tax_rate' => $data['tax_rate'] ?? config('erp.tax_rate', 18),
                'created_by' => auth()->id(),
                'seller_id' => $data['seller_id'] ?? auth()->id(),
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                SaleItem::create([
                    ...$item,
                    'sale_id' => $sale->id,
                    'unit_cost' => $product->average_cost ?? $product->cost,
                    'unit' => $item['unit'] ?? $product->unit,
                ]);
            }

            $sale->recalculateTotals();

            return $sale->fresh(['items.product', 'customer', 'warehouse']);
        });
    }

    public function confirm(Sale $sale): Sale
    {
        if ($sale->status !== SaleStatus::Quotation) {
            throw new \DomainException('Solo se pueden confirmar cotizaciones.');
        }

        return DB::transaction(function () use ($sale) {
            $warehouse = Warehouse::findOrFail($sale->warehouse_id);

            // Verificar stock disponible para todos los items
            foreach ($sale->items as $item) {
                $product = $item->product;
                $inventory = \App\Domains\Inventory\Models\Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                $available = $inventory ? (float) $inventory->available_quantity : 0;

                if ($product->is_producible && $available < $item->quantity) {
                    throw new \DomainException(
                        "Stock insuficiente de '{$product->name}'. Disponible: {$available}, Requerido: {$item->quantity}"
                    );
                }
            }

            // Descontar stock
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product->is_producible || $product->is_sellable) {
                    $this->stockService->recordMovement(
                        $product,
                        $warehouse,
                        $item->quantity,
                        MovementType::SaleExit,
                        (float) $item->unit_cost,
                        $sale->order_number,
                        "Venta {$sale->order_number}",
                        auth()->id(),
                        $sale
                    );
                }
            }

            $sale->update([
                'status' => SaleStatus::Confirmed,
                'confirmed_at' => now(),
            ]);

            return $sale->fresh();
        });
    }

    public function invoice(Sale $sale, array $invoiceData): Sale
    {
        if ($sale->status !== SaleStatus::Confirmed) {
            throw new \DomainException('Solo se pueden facturar ventas confirmadas.');
        }

        $sale->update([
            'status' => SaleStatus::Invoiced,
            'invoice_number' => $invoiceData['invoice_number'] ?? null,
            'invoice_series' => $invoiceData['invoice_series'] ?? null,
            'invoice_date' => $invoiceData['invoice_date'] ?? now()->toDateString(),
        ]);

        return $sale->fresh();
    }

    public function registerPayment(Sale $sale, array $paymentData): Sale
    {
        DB::transaction(function () use ($sale, $paymentData) {
            $sale->payments()->create([
                ...$paymentData,
                'created_by' => auth()->id(),
            ]);

            $totalPaid = $sale->payments()->sum('amount');
            if ($totalPaid >= $sale->total) {
                $sale->update(['status' => SaleStatus::Paid]);
            }
        });

        return $sale->fresh(['payments']);
    }

    public function cancel(Sale $sale, ?string $reason = null): Sale
    {
        if ($sale->status === SaleStatus::Paid) {
            throw new \DomainException('No se puede cancelar una venta pagada. Debe emitir una nota de crédito.');
        }

        return DB::transaction(function () use ($sale, $reason) {
            // Si ya fue confirmada, devolver stock
            if ($sale->status === SaleStatus::Confirmed || $sale->status === SaleStatus::Invoiced) {
                $warehouse = Warehouse::findOrFail($sale->warehouse_id);
                foreach ($sale->items as $item) {
                    $this->stockService->recordMovement(
                        $item->product,
                        $warehouse,
                        $item->quantity,
                        MovementType::PositiveAdjustment,
                        (float) $item->unit_cost,
                        $sale->order_number,
                        "Devolución por cancelación de venta {$sale->order_number}"
                    );
                }
            }

            $sale->update([
                'status' => SaleStatus::Cancelled,
                'notes' => ($sale->notes ? $sale->notes . "\n" : '') . "Cancelado: {$reason}",
            ]);

            return $sale->fresh();
        });
    }

    private function generateOrderNumber(string $type = 'sale'): string
    {
        $year = now()->format('Y');

        if ($type === 'quotation') {
            $seq = DB::selectOne("SELECT nextval('quotation_seq') AS seq")->seq;
            return sprintf('COT-%s-%05d', $year, $seq);
        }

        $seq = DB::selectOne("SELECT nextval('sale_seq') AS seq")->seq;
        return sprintf('VTA-%s-%05d', $year, $seq);
    }
}
