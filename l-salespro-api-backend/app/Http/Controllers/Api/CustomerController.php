<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Requests\Customer\FilterCustomersRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Services\LeysCustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    protected $service;

    public function __construct(LeysCustomerService $service)
    {
        $this->service = $service;
    }

    public function index(FilterCustomersRequest $request): JsonResponse
    {
        $customers = $this->service->getAll($request->validated());

        return response()->json([
            'success' => true,
            'data' => new CustomerCollection($customers),
            'message' => 'Customers retrieved successfully.'
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            $customer = $this->service->getById($id);
            return response()->json([
                'success' => true,
                'data' => new CustomerResource($customer),
                'message' => 'Customer details retrieved.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function store(CreateCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new CustomerResource($customer),
            'message' => 'Customer created successfully.'
        ], 201);
    }

    public function update(UpdateCustomerRequest $request, $id): JsonResponse
    {
        try {
            $customer = $this->service->update($id, $request->validated());
            return response()->json([
                'success' => true,
                'data' => new CustomerResource($customer),
                'message' => 'Customer updated successfully.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Customer soft deleted successfully.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function orders($id): JsonResponse
    {
        try {
            $orders = $this->service->getOrders($id);
            return response()->json([
                'success' => true,
                'data' => $orders,  // Use OrderResource if available
                'message' => 'Customer order history retrieved.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function creditStatus($id): JsonResponse
    {
        try {
            $status = $this->service->getCreditStatus($id);
            return response()->json([
                'success' => true,
                'data' => $status,
                'message' => 'Customer credit status retrieved.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function mapData(): JsonResponse
    {
        $mapData = $this->service->getMapData();

        return response()->json([
            'success' => true,
            'data' => $mapData,
            'message' => 'Customer map data retrieved.'
        ]);
    }
}