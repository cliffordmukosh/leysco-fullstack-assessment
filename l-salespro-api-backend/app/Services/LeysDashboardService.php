<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeysDashboardService
{
    public function getSummary(array $filters)
    {
        $cacheKey = 'dashboard_summary_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            $query = Order::query();

            $this->applyDateFilter($query, $filters['period'] ?? 'month');

            $totalSales = $query->sum('total_amount');
            $orderCount = $query->count();
            $avgOrderValue = $orderCount ? $totalSales / $orderCount : 0;
            $totalSold = OrderItem::whereIn('order_id', $query->select('id'))
                ->sum('quantity');

            $avgStock = DB::table('inventory')->avg('quantity') ?? 1;
            $turnover = $avgStock ? $totalSold / $avgStock : 0;

            return [
                'total_sales' => round($totalSales, 2),
                'order_count' => $orderCount,
                'average_order_value' => round($avgOrderValue, 2),
                'inventory_turnover_rate' => round($turnover, 2),
                'period' => $filters['period'] ?? 'month'
            ];
        });
    }

public function getSalesPerformance(array $filters)
{
    $cacheKey = 'sales_performance_' . md5(json_encode($filters));

    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
        $query = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date');

        $this->applyDateFilter($query, $filters['period'] ?? 'month');

        return $query->get(); // ← return raw query result (Eloquent Collection)
    });
}


    public function getInventoryStatus()
    {
        return Cache::remember('inventory_status', now()->addMinutes(10), function () {
            return Category::with(['products' => function ($q) {
                $q->withSum('inventory', 'quantity');
            }])
                ->get()
                ->map(function ($category) {
                    $totalStock = $category->products->sum('inventory_sum_quantity');
                    return [
                        'category_name' => $category->name,
                        'total_products' => $category->products->count(),
                        'total_stock' => $totalStock ?? 0,
                    ];
                });
        });
    }

    public function getTopProducts(array $filters)
    {
        $cacheKey = 'top_products_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            $query = OrderItem::query()
                ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(5);

            if (isset($filters['period'])) {
                $query->whereHas('order', function ($q) use ($filters) {
                    $this->applyDateFilter($q, $filters['period']);
                });
            }

            return $query->get();
        });
    }

    protected function applyDateFilter($query, string $period): void
    {
        $now = Carbon::now();

        match ($period) {
            'today'    => $query->whereDate('created_at', $now->toDateString()),
            'week'     => $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]),
            'month'    => $query->whereMonth('created_at', $now->month)
                               ->whereYear('created_at', $now->year),
            'quarter'  => $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]),
            'year'     => $query->whereYear('created_at', $now->year),
            default    => $query->whereMonth('created_at', $now->month),
        };
    }
}