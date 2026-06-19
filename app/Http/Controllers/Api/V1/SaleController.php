<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Sales\Models\Sale;
use App\Domains\Sales\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SaleController extends Controller
{
    public function __construct(private SaleService $service)
    {
        $this->middleware('can:view sales')->only(['index', 'show']);
        $this->middleware('can:create sales')->only(['store']);
        $this->middleware('can:edit sales')->only(['update']);
        $this->middleware('can:confirm sales')->only(['confirm', 'invoice', 'registerPayment']);
        $this->middleware('can:cancel sales')->only(['cancel']);
    }

    public function index(Request $request): JsonResponse
    {
        $sales = Sale::with(['customer', 'warehouse'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->from, fn($q) => $q->where('sale_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->where('sale_date', '<=', $request->to))
            ->orderByDesc('sale_date')
            ->paginate($request->per_page ?? 25);

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'in:sale,quotation'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'sale_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $items = $validated['items'];
            unset($validated['items']);

            $sale = $this->service->create($validated, $items);

            return response()->json([
                'message' => "Venta {$sale->order_number} creada correctamente.",
                'data' => $sale,
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'warehouse', 'items.product', 'payments', 'seller', 'createdBy']);

        return response()->json(['data' => $sale]);
    }

    public function update(Request $request, Sale $sale): JsonResponse
    {
        if (!in_array($sale->status->value, ['quotation'])) {
            return response()->json(['message' => 'Solo se pueden editar cotizaciones.'], 422);
        }

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $sale->update($validated);
        $sale->recalculateTotals();

        return response()->json([
            'message' => 'Venta actualizada.',
            'data' => $sale->fresh(['items.product']),
        ]);
    }

    public function confirm(Request $request, Sale $sale): JsonResponse
    {
        try {
            $sale = $this->service->confirm($sale);
            return response()->json([
                'message' => "Venta {$sale->order_number} confirmada. Stock descontado.",
                'data' => $sale,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function invoice(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:60'],
            'invoice_series' => ['nullable', 'string', 'max:10'],
            'invoice_date' => ['required', 'date'],
        ]);

        $sale = $this->service->invoice($sale, $validated);

        return response()->json([
            'message' => 'Factura registrada correctamente.',
            'data' => $sale,
        ]);
    }

    public function registerPayment(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'in:cash,card,transfer,check,other'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $sale = $this->service->registerPayment($sale, $validated);

        return response()->json([
            'message' => 'Pago registrado.',
            'total_paid' => $sale->total_paid,
            'balance_due' => $sale->balance_due,
            'status' => $sale->status->label(),
        ]);
    }

    public function cancel(Request $request, Sale $sale): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5']]);

        try {
            $sale = $this->service->cancel($sale, $request->reason);
            return response()->json(['message' => 'Venta cancelada.', 'data' => $sale]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
