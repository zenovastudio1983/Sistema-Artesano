<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Reports\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    public function __construct(private ReportService $service)
    {
        $this->middleware('can:view reports');
    }

    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getDashboardKpis(),
            'cached_at' => now()->toISOString(),
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'group_by' => ['nullable', 'in:day,week,month'],
        ]);

        return response()->json([
            'data' => $this->service->getSalesReport(
                $request->from,
                $request->to,
                $request->customer_id,
                $request->group_by ?? 'day'
            ),
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);

        return response()->json([
            'data' => $this->service->getPurchasesReport(
                $request->from,
                $request->to,
                $request->supplier_id
            ),
        ]);
    }

    public function inventory(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getInventoryReport(),
        ]);
    }

    public function production(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'data' => $this->service->getProductionCostReport($request->from, $request->to),
        ]);
    }

    public function profitability(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $salesReport = $this->service->getSalesReport($request->from, $request->to);
        $productionReport = $this->service->getProductionCostReport($request->from, $request->to);

        return response()->json([
            'data' => [
                'sales' => $salesReport['totals'],
                'production' => $productionReport['totals'],
                'top_products' => $salesReport['top_products'],
                'period' => ['from' => $request->from, 'to' => $request->to],
            ],
        ]);
    }
}
