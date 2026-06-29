<?php

namespace App\Jobs;

use App\Models\AutoVoucher;
use App\Models\Order;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Order $order, protected AutoVoucher $autoVoucher)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->order->variation->providerType($this->order)->verify($this->autoVoucher);
        } catch (Exception $e) {
            Log::error('VerifyOrderStatus failed, retrying in 60s', [
                'order_id' => $this->order->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            $this->release(60);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('VerifyOrderStatus permanently failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
