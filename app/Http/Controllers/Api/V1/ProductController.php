<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Inventory\Models\Warehouse;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Products\Models\Product;
use App\Jobs\RecalculateProductCosts;
use App\Support\Enums\ProductStatus;
use App\Support\Enums\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view products')->only(['index', 'show', 'inventory', 'kardex', 'recipes']);
        $this->middleware('can:create products')->only(['store']);
        $this->middleware('can:edit products')->only(['update']);
        $this->middleware('can:delete products')->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->type, fn($q) => $q->byType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id));

        if ($request->with_stock) {
            $query->with(['inventory.warehouse']);
        }

        $products = $query
            ->orderBy($request->sort_by ?? 'name', $request->sort_dir ?? 'asc')
            ->paginate($request->per_page ?? config('erp.per_page', 25));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(ProductType::class)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:20'],
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock_minimum' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
            'is_purchasable' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_producible' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::create(array_merge($validated, [
            'standard_cost' => $validated['cost'],
            'average_cost' => $validated['cost'],
            'status' => $validated['status'] ?? ProductStatus::Active,
        ]));

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'data' => $product->fresh(['category']),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'inventory.warehouse', 'recipes.ingredients.product']);

        return response()->json(['data' => $product]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['sometimes', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'name' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', Rule::enum(ProductType::class)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock_minimum' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
            'is_purchasable' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_producible' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldCost = (float) $product->cost;
        $product->update($validated);

        if (isset($validated['cost']) && abs($validated['cost'] - $oldCost) > 0.0001) {
            RecalculateProductCosts::dispatch($product->id, 'cost_update');
        }

        return response()->json([
            'message' => 'Producto actualizado correctamente.',
            'data' => $product->fresh(['category']),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->inventory()->where('quantity', '>', 0)->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un producto con stock disponible.',
            ], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }

    public function inventory(Product $product): JsonResponse
    {
        $inventory = $product->inventory()->with('warehouse')->get();

        return response()->json([
            'data' => $inventory,
            'total_stock' => $inventory->sum('quantity'),
            'total_reserved' => $inventory->sum('reserved_quantity'),
            'available_stock' => $inventory->sum('available_quantity'),
            'total_value' => $inventory->sum('total_value'),
        ]);
    }

    public function kardex(Request $request, Product $product, StockService $stockService): JsonResponse
    {
        $warehouse = Warehouse::findOrFail($request->warehouse_id ?? Warehouse::where('is_default', true)->first()?->id);

        $movements = $stockService->getKardex(
            $product,
            $warehouse,
            $request->from,
            $request->to
        );

        return response()->json([
            'data' => $movements,
            'product' => $product->only(['id', 'sku', 'name', 'unit']),
            'warehouse' => $warehouse->only(['id', 'code', 'name']),
        ]);
    }

    public function recipes(Product $product): JsonResponse
    {
        $recipes = $product->recipes()->with(['ingredients.product', 'additionalCosts'])->get();

        return response()->json(['data' => $recipes]);
    }
}
