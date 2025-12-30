<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // logging 
        Log::info('LOW STOCK ALERT', [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'sku' => $this->product->sku,
            'reorder_level' => $this->product->reorder_level,
            'total_stock' => $this->product->inventory->sum('quantity'),
            'timestamp' => now()->toDateTimeString(),
        ]);

      
        // - Send email to managers
        // - Create Notification record
        // - Notify users with 'low_stock' preference

        // $this->product->notify(new LowStockNotification($this->product));
        // Mail::to('manager@leysco.co.ke')->send(new LowStockMail($this->product));
    }
}