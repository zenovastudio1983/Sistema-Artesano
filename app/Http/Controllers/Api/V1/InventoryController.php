<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Products\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(private StockService $stockService)
    {
        $this->middleware('can:view inventory')->only(['index', 'show']);
        $this->middleware('can:adjust inventory')->only(['adjust', 'transfer']);
    }

    public function index(Request $request): JsonResponse
    {
        $inventory = DB::table('v_inventory_status')
            ->when($request->search, fn($q) => $q->where('product_name', 'ilike', "%{$request->search}%")
                ->orWhere('sku', 'ilike', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('stock_status', $request->status))
            ->when($request->type, fn($q) => $q->where('product_type', $request->type))
            ->where('product_status', 'active')
            ->orderBy($request->sort_by ?? 'product_name')
            ->paginate($request->per_page ?? 25);

        return response()->json($inventory);
    }

    public function show(Product $product): JsonResponse
    {
        $inventory = $product->inventory()->with('warehouse')->get();

        return response()->json([
            'data' => [
                'product' => $product->only(['id', 'sku', 'name', 'unit', 'stock_minimum']),
                'inventory' => $inventory,
                'total_stock' => $inventory->sum('quantity'),
                'available_stock' => $inventory->sum('available_quantity'),
                'total_value' => $inventory->sum('total_value'),
                'status' => $product->isOutOfStock() ? 'out_of_stock' : ($product->isLowStock() ? 'low' : 'ok'),
            ],
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $movement = $this->stockService->adjust(
            $product,
            $warehouse,
            (float) $request->new_quantity,
            $request->notes,
            auth()->id()
        );

        return response()->json([
            'message' => 'Ajuste de inventario registrado correctamente.',
            'data' => $movement,
        ], 201);
    }

    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $from = Warehouse::findOrFail($request->from_warehouse_id);
        $to = Warehouse::findOrFail($request->to_warehouse_id);

        $movements = $this->stockService->transfer(
            $product, $from, $to,
            (float) $request->quantity,
            $request->notes,
            auth()->id()
        );

        return response()->json([
            'message' => 'Transferencia realizada correctamente.',
            'exit_movement' => $movements['exit'],
            'entry_movement' => $movements['entry'],
        ], 201);
    }

    public function lowStock(): JsonResponse
    {
        $products = $this->stockService->getLowStockProducts();

        return response()->json([
            'data' => $products,
            'count' => $products->count(),
        ]);
    }
}
