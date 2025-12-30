<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\App;

class CheckCreditLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerRepo = App::make(CustomerRepositoryInterface::class);

        $customerId = $request->input('customer_id');
        if (!$customerId) {
            return $next($request); // Skip if no customer (validation will catch it)
        }

        $customer = $customerRepo->find($customerId);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        // Calculate order total from request data
        $items = $request->input('items', []);
        $discount = $request->input('discount', []);

        // Reuse calculation logic from OrderRepository
        $orderRepo = App::make(\App\Repositories\Interfaces\OrderRepositoryInterface::class);
        $total = $orderRepo->calculateTotal($items, $discount);

        $availableCredit = $customer->credit_limit - $customer->current_balance;

        if ($total['total_amount'] > $availableCredit) {
            return response()->json([
                'success' => false,
                'message' => 'Order exceeds available credit limit. Available: ' . number_format($availableCredit, 2),
                'available_credit' => $availableCredit,
                'requested_total' => $total['total_amount'],
            ], 403);
        }

        return $next($request);
    }
}