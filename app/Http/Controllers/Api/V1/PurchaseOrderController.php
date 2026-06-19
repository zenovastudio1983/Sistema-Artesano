<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseService $service)
    {
        $this->middleware('can:view purchases')->only(['index', 'show']);
        $this->middleware('can:create purchase orders')->only(['store']);
        $this->middleware('can:edit purchase orders')->only(['update']);
        $this->middleware('can:approve purchase orders')->only(['send']);
        $this->middleware('can:receive purchase orders')->only(['receive']);
        $this->middleware('can:cancel purchase orders')->only(['cancel']);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->orderByDesc('order_date')
            ->paginate($request->per_page ?? 25);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $order = $this->service->create($validated, $items);

        return response()->json([
            'message' => "OC {$order->order_number} creada correctamente.",
            'data' => $order,
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'receipts.items.product']);

        return response()->json(['data' => $purchaseOrder]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status->value !== 'draft') {
            return response()->json(['message' => 'Solo se pueden editar OC en borrador.'], 422);
        }

        $validated = $request->validate([
            'expected_date' => ['nullable', 'date'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchaseOrder->update($validated);

        return response()->json(['message' => 'OC actualizada.', 'data' => $purchaseOrder->fresh()]);
    }

    public function send(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $order = $this->service->send($purchaseOrder);

        return response()->json(['message' => "OC {$order->order_number} enviada al proveedor.", 'data' => $order]);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $request->validate([
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'receipt_date' => ['nullable', 'date'],
            'supplier_invoice' => ['nullable', 'string', 'max:60'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:60'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        $receipt = $this->service->receive($purchaseOrder, $request->except('items'), $request->items);

        return response()->json([
            'message' => "Recepción {$receipt->receipt_number} registrada. Stock actualizado.",
            'data' => $receipt,
        ], 201);
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5']]);

        $order = $this->service->cancel($purchaseOrder, $request->reason);

        return response()->json(['message' => 'OC cancelada.', 'data' => $order]);
    }
}
