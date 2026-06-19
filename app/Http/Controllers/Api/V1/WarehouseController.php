<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Inventory\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return response()->json(['data' => $warehouses]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('edit settings');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'is_default' => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create($validated);

        return response()->json(['message' => 'Almacén creado.', 'data' => $warehouse], 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        $summary = \Illuminate\Support\Facades\DB::table('inventory')
            ->where('warehouse_id', $warehouse->id)
            ->selectRaw('COUNT(*) as product_count, SUM(total_value) as total_value')
            ->first();

        return response()->json([
            'data' => array_merge($warehouse->toArray(), ['summary' => $summary]),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorize('edit settings');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $warehouse->update($validated);

        return response()->json(['message' => 'Almacén actualizado.', 'data' => $warehouse->fresh()]);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('edit settings');

        if ($warehouse->is_default) {
            return response()->json(['message' => 'No se puede eliminar el almacén por defecto.'], 422);
        }

        if ($warehouse->inventory()->where('quantity', '>', 0)->exists()) {
            return response()->json(['message' => 'No se puede eliminar un almacén con stock.'], 422);
        }

        $warehouse->delete();

        return response()->json(['message' => 'Almacén eliminado.']);
    }
}
