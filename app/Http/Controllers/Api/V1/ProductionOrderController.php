<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Production\Models\ProductionOrder;
use App\Domains\Production\Services\ProductionService;
use App\Support\Enums\ProductionOrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductionOrderController extends Controller
{
    public function __construct(private ProductionService $service)
    {
        $this->middleware('can:view production')->only(['index', 'show']);
        $this->middleware('can:create production orders')->only(['store']);
        $this->middleware('can:edit production orders')->only(['update']);
        $this->middleware('can:start production')->only(['start']);
        $this->middleware('can:finish production')->only(['finish', 'registerProduction']);
        $this->middleware('can:cancel production')->only(['cancel']);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = ProductionOrder::with(['product', 'warehouse', 'assignedUser'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 25);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'recipe_id' => ['nullable', 'exists:recipes,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'planned_quantity' => ['required', 'numeric', 'min:0.001'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $order = $this->service->create($validated);
            return response()->json([
                'message' => "Orden {$order->order_number} creada correctamente.",
                'data' => $order,
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->load([
            'product', 'recipe.ingredients.product', 'warehouse',
            'materials.product', 'logs.createdBy', 'assignedUser', 'createdBy',
        ]);

        return response()->json(['data' => $productionOrder]);
    }

    public function update(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        if (!$productionOrder->status->isEditable()) {
            return response()->json(['message' => 'Esta orden no puede ser modificada en su estado actual.'], 422);
        }

        $validated = $request->validate([
            'planned_quantity' => ['sometimes', 'numeric', 'min:0.001'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $productionOrder->update($validated);

        return response()->json([
            'message' => 'Orden actualizada correctamente.',
            'data' => $productionOrder->fresh(),
        ]);
    }

    public function plan(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        return $this->transition($productionOrder, ProductionOrderStatus::Planned, $request->notes);
    }

    public function start(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        return $this->transition($productionOrder, ProductionOrderStatus::InProgress, $request->notes);
    }

    public function finish(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        return $this->transition($productionOrder, ProductionOrderStatus::Finished, $request->notes);
    }

    public function cancel(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $request->validate(['notes' => ['required', 'string', 'min:5']]);

        return $this->transition($productionOrder, ProductionOrderStatus::Cancelled, $request->notes);
    }

    public function registerProduction(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $request->validate([
            'produced_quantity' => ['required', 'numeric', 'min:0.001'],
            'rejected_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->service->registerProduction(
                $productionOrder,
                (float) $request->produced_quantity,
                (float) ($request->rejected_quantity ?? 0)
            );

            return response()->json([
                'message' => 'Producción registrada correctamente.',
                'data' => $order,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function transition(ProductionOrder $order, ProductionOrderStatus $status, ?string $notes = null): JsonResponse
    {
        try {
            $order = $this->service->transitionStatus($order, $status, $notes);
            return response()->json([
                'message' => "Orden → {$status->label()}",
                'data' => $order,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
