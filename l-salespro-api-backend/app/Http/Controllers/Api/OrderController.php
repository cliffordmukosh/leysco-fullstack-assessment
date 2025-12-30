<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Http\Requests\Order\CalculateTotalRequest;
use App\Http\Resources\OrderResource;
use App\Services\LeysOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $service;

    public function __construct(LeysOrderService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $orders = $this->service->getAll(request()->all());

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully.'
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            $order = $this->service->getById($id);
            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
                'message' => 'Order details retrieved.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'errors' => $e->errors(),
            ], 422);
        }
    }

public function store(CreateOrderRequest $request): JsonResponse
{
    Log::info('Order creation request received', [
        'user_id' => auth()->id(),
        'payload' => $request->validated(),
    ]);

    $user = $request->user();

    if (!$user) {
        Log::warning('Unauthenticated order attempt');
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated - order creation requires login',
        ], 401);
    }

    try {
        Log::info('Calling service to create order', ['user_id' => $user->id]);

        $order = $this->service->create(
            $request->validated(),
            $user->id
        );

        Log::info('Order created successfully', ['order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
            'message' => 'Order created successfully.'
        ], 201);
    } catch (ValidationException $e) {
        Log::error('Order validation failed', $e->errors());
        return response()->json([
            'success' => false,
            'message' => 'Order creation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Unexpected error creating order', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Server error during order creation',
        ], 500);
    }
}

    public function updateStatus(UpdateStatusRequest $request, $id): JsonResponse
    {
        try {
            $order = $this->service->updateStatus($id, $request->validated()['status']);

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
                'message' => 'Order status updated.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status update failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function invoice($id): JsonResponse
    {
        try {
            $data = $this->service->getInvoiceData($id);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Invoice data generated.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function calculateTotal(CalculateTotalRequest $request): JsonResponse
    {
        $total = $this->service->calculateTotal($request->validated());

        return response()->json([
            'success' => true,
            'data' => $total,
            'message' => 'Total calculated successfully.'
        ]);
    }
}