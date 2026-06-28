<?php

namespace App\Services\TopupProvider;

use App\Models\AutoVoucher;
use App\Models\Order;

abstract class TopupProviderService
{
    protected $baseUrl;

    protected $apiKey;

    public function __construct(public Order $order)
    {
        $this->baseUrl = rtrim(gs()->free_fire_server_url, '/');
        $this->apiKey = gs()->free_fire_server_api_key;
    }

    abstract public function placeOrder(AutoVoucher $autoVoucher);
}
