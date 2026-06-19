<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Sales\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view customers')->only(['index', 'show']);
        $this->middleware('can:create customers')->only(['store']);
        $this->middleware('can:edit customers')->only(['update']);
        $this->middleware('can:delete customers')->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $customers = Customer::when($request->search, fn($q) => $q->search($request->search))
            ->when($request->active_only, fn($q) => $q->active())
            ->orderBy('business_name')
            ->paginate($request->per_page ?? 25);

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:200'],
            'trade_name' => ['nullable', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'tax_type' => ['nullable', 'in:RUC,DNI,CE,PASSPORT'],
            'customer_type' => ['nullable', 'in:retail,wholesale,corporate'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'payment_days' => ['nullable', 'integer', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['code'] = $this->generateCode();
        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Cliente creado correctamente.',
            'data' => $customer,
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['sales' => fn($q) => $q->confirmed()->latest()->limit(10)]);

        return response()->json([
            'data' => array_merge($customer->toArray(), [
                'total_sales' => $customer->total_sales,
                'available_credit' => $customer->available_credit,
            ]),
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:200'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'payment_days' => ['nullable', 'integer', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return response()->json(['message' => 'Cliente actualizado.', 'data' => $customer->fresh()]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->sales()->confirmed()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un cliente con ventas confirmadas.',
            ], 422);
        }

        $customer->delete();

        return response()->json(['message' => 'Cliente eliminado.']);
    }

    private function generateCode(): string
    {
        $seq = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('customer_code_seq') AS seq")->seq;
        return sprintf('CLI-%04d', $seq);
    }
}
