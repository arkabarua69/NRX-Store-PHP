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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOrderRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $backoff = [60, 120, 240, 480, 960];

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
        $provider = $this->order->variation->provider;
        $cacheKey = 'order_requests_rate_limit_' . $provider;
        $rateLimit = 3;

        // Get the current count of requests in the last minute
        $currentRequests = Cache::get($cacheKey, 0);

        if ($currentRequests >= $rateLimit) {
            $this->release(60);
            return;
        }

        // Increment the request count
        Cache::put($cacheKey, $currentRequests + 1, now()->addMinute());

        try {
            $this->order->variation->providerType($this->order)->placeOrder($this->autoVoucher);
        } catch (Exception $e) {
            $this->release(60);
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil()
    {
        return now()->addHours(12);
    }
}
