<?php

namespace App\Helpers;

use App\Models\Order;

class LeyscoHelpers
{
    /**
     * Format currency in KES with /=
     * Example: 10000.50 → "KES 10,000.50 /="
     */
    public static function formatCurrency(float $amount): string
    {
        return 'KES ' . number_format($amount, 2, '.', ',') . ' /=';
    }

    /**
     * Generate unique order number: ORD-YYYY-MM-XXX
     * Example: ORD-2025-12-001
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Y-m');
        $lastOrder = Order::latest()->first();

        $number = 1;
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -3);
            $number = $lastNumber + 1;
        }

        return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate tax amount
     *
     * @param float $amount Base amount after discount
     * @param float $rate Tax rate in percent (default 16%)
     * @return float Tax amount
     */
    public static function calculateTax(float $amount, float $rate = 16.0): float
    {
        return $amount * ($rate / 100);
    }
}