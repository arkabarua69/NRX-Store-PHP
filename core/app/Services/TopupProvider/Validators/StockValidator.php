<?php

namespace App\Services\TopupProvider\Validators;

use App\Models\AutoVoucher;
use App\Models\Order;
use App\Models\Variation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockValidator
{
    public function validateBeforePlacement(Order $order): bool
    {
        $variation = $order->variation;
        $provider = $variation->provider;

        if (!$provider) {
            return $this->localStockCheck($variation);
        }

        try {
            $method = 'validateStock' . $provider;
            if (method_exists($this, $method)) {
                return $this->$method($variation);
            }
        } catch (\Exception $e) {
            Log::warning("Stock validation failed for provider {$provider}: " . $e->getMessage());
        }

        return $this->localStockCheck($variation);
    }

    private function localStockCheck(Variation $variation): bool
    {
        $autoVoucherCount = AutoVoucher::where('variation_id', $variation->id)
            ->where('status', 1)
            ->count();

        return $autoVoucherCount > 0 && $variation->stock > 0;
    }

    private function validateStockFreeFire(Variation $variation): bool
    {
        $baseUrl = rtrim(gs()->free_fire_server_url, '/');
        $apiKey = gs()->free_fire_server_api_key;

        if (!$baseUrl || !$apiKey) {
            return $this->localStockCheck($variation);
        }

        try {
            $response = Http::withHeaders([
                'RA-SECRET-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])->get($baseUrl . '/stock', [
                'denom' => $variation->provider_product_id,
            ]);

            if ($response->successful() && isset($response->json()['stock'])) {
                $remoteStock = (int) $response->json()['stock'];
                return $remoteStock > 0 && $this->localStockCheck($variation);
            }
        } catch (\Exception $e) {
            Log::warning("FreeFire remote stock check failed: " . $e->getMessage());
        }

        return $this->localStockCheck($variation);
    }

    private function validateStockUnipin(Variation $variation): bool
    {
        return $this->localStockCheck($variation);
    }
}
