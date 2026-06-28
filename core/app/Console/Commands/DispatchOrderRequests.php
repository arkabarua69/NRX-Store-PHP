<?php

namespace App\Console\Commands;

use App\Constants\OrderStatus;
use App\Jobs\SendOrderRequest;
use App\Models\AutoVoucher;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchOrderRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch order requests in batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('status', OrderStatus::AUTOPROCESSING)
            ->where('attempts', 0)
            ->take(3)
            ->get();

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                $order->attempts = 1;
                $order->save();

                $autoVoucher = AutoVoucher::where('code', $order->voucher_code)->first();
                if (!$autoVoucher) {
                    continue;
                }

                SendOrderRequest::dispatch($order, $autoVoucher)->onQueue('order');
            }
        });

        $this->info("Dispatched 3 order requests.");
    }
}
