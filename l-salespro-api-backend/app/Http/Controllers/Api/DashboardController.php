<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardAnalyticsRequest;
use App\Http\Resources\DashboardSummaryResource;
use App\Http\Resources\InventoryStatusResource;
use App\Http\Resources\SalesPerformanceResource;
use App\Http\Resources\TopProductResource;
use App\Services\LeysDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(LeysDashboardService $service)
    {
        $this->service = $service;
    }

    public function summary(DashboardAnalyticsRequest $request): JsonResponse
    {
        $data = $this->service->getSummary($request->validated());

        return response()->json([
            'success' => true,
            'data' => new DashboardSummaryResource($data),
            'message' => 'Dashboard summary retrieved successfully'
        ]);
    }

public function salesPerformance(DashboardAnalyticsRequest $request): JsonResponse
{
    $data = $this->service->getSalesPerformance($request->validated());

    return response()->json([
        'success' => true,
        'data'    => SalesPerformanceResource::collection($data),  // ← now useful
        'message' => 'Sales performance data retrieved',
    ]);
}

    public function inventoryStatus(Request $request): JsonResponse
    {
        $data = $this->service->getInventoryStatus();

        return response()->json([
            'success' => true,
            'data' => InventoryStatusResource::collection($data),
            'message' => 'Inventory status by category retrieved'
        ]);
    }

    public function topProducts(DashboardAnalyticsRequest $request): JsonResponse
    {
        $data = $this->service->getTopProducts($request->validated());

        return response()->json([
            'success' => true,
            'data' => TopProductResource::collection($data),
            'message' => 'Top selling products retrieved'
        ]);
    }
}