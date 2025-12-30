<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\ReserveStockRequest;
use App\Http\Requests\Product\ReleaseStockRequest;
use App\Http\Requests\Product\FilterProductsRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Services\LeysProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(LeysProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(FilterProductsRequest $request): JsonResponse
    {
        $products = $this->productService->getAll($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ProductCollection($products),
            'message' => 'Products retrieved successfully.'
        ]);
    }

    public function show($id): JsonResponse
    {
        $product = $this->productService->getById($id);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product details retrieved.'
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product created successfully.'
        ], 201);
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = $this->productService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product updated successfully.'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->productService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product soft deleted successfully.'
        ]);
    }

    public function stock($id): JsonResponse
    {
        $stock = $this->productService->getStock($id);

        return response()->json([
            'success' => true,
            'data' => $stock,
            'message' => 'Real-time stock across warehouses retrieved.'
        ]);
    }

    public function reserve(ReserveStockRequest $request, $id): JsonResponse
    {
        $this->productService->reserve($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Stock reserved successfully.'
        ]);
    }

    public function release(ReleaseStockRequest $request, $id): JsonResponse
    {
        $this->productService->release($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Stock released successfully.'
        ]);
    }

    public function lowStock(): JsonResponse
    {
        $lowStock = $this->productService->getLowStock();

        return response()->json([
            'success' => true,
            'data' => $lowStock,
            'message' => 'Low stock products retrieved.'
        ]);
    }
}