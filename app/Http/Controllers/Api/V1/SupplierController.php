<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Purchases\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view suppliers')->only(['index', 'show']);
        $this->middleware('can:create suppliers')->only(['store']);
        $this->middleware('can:edit suppliers')->only(['update']);
        $this->middleware('can:delete suppliers')->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::when($request->search, fn($q) => $q->search($request->search))
            ->when($request->active_only, fn($q) => $q->active())
            ->orderBy('business_name')
            ->paginate($request->per_page ?? 25);

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:200'],
            'trade_name' => ['nullable', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:30', 'unique:suppliers,tax_id'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'payment_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'currency' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['code'] = $this->generateCode();
        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Proveedor creado correctamente.',
            'data' => $supplier,
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);

        return response()->json(['data' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:200'],
            'trade_name' => ['nullable', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:30', Rule::unique('suppliers')->ignore($supplier->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'payment_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return response()->json(['message' => 'Proveedor actualizado.', 'data' => $supplier->fresh()]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->purchaseOrders()->active()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un proveedor con órdenes de compra activas.',
            ], 422);
        }

        $supplier->delete();

        return response()->json(['message' => 'Proveedor eliminado.']);
    }

    private function generateCode(): string
    {
        $seq = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('supplier_code_seq') AS seq")->seq;
        return sprintf('PROV-%04d', $seq);
    }
}
