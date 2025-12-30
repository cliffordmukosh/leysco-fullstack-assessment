<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StockTransferRequest;
use App\Http\Requests\Warehouse\FilterTransferHistoryRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\StockTransferResource;
use App\Services\LeysWarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
;

class WarehouseController extends Controller
{
    protected $service;

    public function __construct(LeysWarehouseService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/warehouses
     * List all warehouses
     */
    public function index(): JsonResponse
    {
        $warehouses = $this->service->getAllWarehouses();

        return response()->json([
            'success' => true,
            'data'    => WarehouseResource::collection($warehouses),
            'message' => 'Warehouses retrieved successfully',
        ]);
    }

    /**
     * GET /api/v1/warehouses/{id}/inventory
     * Warehouse-specific inventory + capacity monitoring
     */
    public function inventory($id): JsonResponse
    {
        try {
            $data = $this->service->getWarehouseInventory($id);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'message' => 'Warehouse inventory retrieved successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * POST /api/v1/stock-transfers
     * Transfer stock between warehouses
     */
    public function storeTransfer(StockTransferRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated - stock transfer requires a logged-in user',
            ], 401);
        }

        try {
            $transfer = $this->service->transferStock(
                $request->validated(),
                $user->id  // Pass authenticated user ID explicitly
            );

            return response()->json([
                'success' => true,
                'data'    => new StockTransferResource($transfer),
                'message' => 'Stock transfer completed successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // \Log::error('Stock transfer failed', [
            //     'error' => $e->getMessage(),
            //     'data'  => $request->validated(),
            //     'user_id' => $user->id,
            // ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process stock transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/stock-transfers
     * Transfer history (paginated)
     */
    public function transferHistory(FilterTransferHistoryRequest $request): JsonResponse
    {
        $history = $this->service->getTransferHistory($request->validated());

        return response()->json([
            'success' => true,
            'data'    => StockTransferResource::collection($history),
            'meta'    => [
                'current_page' => $history->currentPage(),
                'per_page'     => $history->perPage(),
                'total'        => $history->total(),
                'last_page'    => $history->lastPage(),
            ],
            'message' => 'Transfer history retrieved successfully',
        ]);
    }
}