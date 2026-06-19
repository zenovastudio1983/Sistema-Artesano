<?php

namespace App\Domains\Purchases\Services;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Products\Models\Product;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Models\PurchaseReceipt;
use App\Support\Enums\MovementType;
use App\Support\Enums\PurchaseOrderStatus;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private StockService $stockService) {}

    public function create(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $order = PurchaseOrder::create([
                ...$data,
                'order_number' => $this->generateOrderNumber(),
                'status' => PurchaseOrderStatus::Draft,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $order->recalculateTotals();

            return $order->fresh(['items.product', 'supplier', 'warehouse']);
        });
    }

    public function send(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== PurchaseOrderStatus::Draft) {
            throw new \DomainException('Solo se pueden enviar órdenes en estado Borrador.');
        }

        $order->update(['status' => PurchaseOrderStatus::Sent]);

        return $order->fresh();
    }

    public function receive(PurchaseOrder $order, array $receiptData, array $receiptItems): PurchaseReceipt
    {
        if (!$order->status->canReceive()) {
            throw new \DomainException("No se puede recibir una OC en estado '{$order->status->label()}'.");
        }

        return DB::transaction(function () use ($order, $receiptData, $receiptItems) {
            $warehouse = Warehouse::findOrFail($receiptData['warehouse_id'] ?? $order->warehouse_id);

            $receipt = PurchaseReceipt::create([
                ...$receiptData,
                'receipt_number' => $this->generateReceiptNumber(),
                'purchase_order_id' => $order->id,
                'warehouse_id' => $warehouse->id,
                'receipt_date' => $receiptData['receipt_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);

            foreach ($receiptItems as $item) {
                $orderItem = $order->items()->findOrFail($item['purchase_order_item_id']);
                $product = Product::findOrFail($orderItem->product_id);

                $receipt->items()->create([
                    'purchase_order_item_id' => $orderItem->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? $orderItem->unit_price,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Registrar movimiento de inventario
                $this->stockService->recordMovement(
                    $product,
                    $warehouse,
                    $item['quantity'],
                    MovementType::PurchaseEntry,
                    $item['unit_price'] ?? $orderItem->unit_price,
                    $receipt->receipt_number,
                    "Recepción OC {$order->order_number}",
                    auth()->id(),
                    $receipt
                );

                // Actualizar cantidad recibida en ítem de OC
                $orderItem->increment('received_quantity', $item['quantity']);

                // Actualizar último costo de compra del producto
                $product->update([
                    'last_purchase_cost' => $item['unit_price'] ?? $orderItem->unit_price,
                ]);
            }

            // Actualizar estado de la OC
            $allReceived = $order->items->every(fn($i) => $i->fresh()->isFullyReceived());
            $newStatus = $allReceived
                ? PurchaseOrderStatus::Received
                : PurchaseOrderStatus::PartiallyReceived;

            $order->update([
                'status' => $newStatus,
                'received_date' => $allReceived ? now()->toDateString() : null,
            ]);

            return $receipt->fresh(['items.product']);
        });
    }

    public function cancel(PurchaseOrder $order, ?string $reason = null): PurchaseOrder
    {
        if ($order->status === PurchaseOrderStatus::Received) {
            throw new \DomainException('No se puede cancelar una OC ya recibida completamente.');
        }

        $order->update([
            'status' => PurchaseOrderStatus::Cancelled,
            'notes' => ($order->notes ? $order->notes . "\n" : '') . "Cancelado: {$reason}",
        ]);

        return $order->fresh();
    }

    private function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $seq = DB::selectOne("SELECT nextval('purchase_order_seq') AS seq")->seq;

        return sprintf('OC-%s-%05d', $year, $seq);
    }

    private function generateReceiptNumber(): string
    {
        $year = now()->format('Y');
        $seq = DB::selectOne("SELECT nextval('purchase_receipt_seq') AS seq")->seq;

        return sprintf('REC-%s-%05d', $year, $seq);
    }
}
