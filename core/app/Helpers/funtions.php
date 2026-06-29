<?php

use App\Models\Setting;
use App\Constants\Status;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\File;


if (!function_exists('user_id')) {
    function user_id()
    {
        return auth()->user()?->id;
    }
}

if (!function_exists('custom_date')) {
    function custom_date($model)
    {
        return date('d-m-Y h:i:s A', strtotime($model->created_at));
    }
}

if (!function_exists('amount')) {
    function amount($data, $decimals = 0)
    {
        $replaced_data = str_replace(",", "", $data);
        return number_format((float) $replaced_data, $decimals, ".", "");
    }
}

if (!function_exists('price')) {
    function price($data, $decimals = 0)
    {
        $replaced_data = str_replace(",", "", $data);
        return gs()->currency_symbol . number_format((float) $replaced_data, $decimals, ".", "");
    }
}

if (!function_exists('gs')) {
    function gs()
    {
        static $settings = null;
        if ($settings === null) {
            $settings = new GeneralSettings();
        }
        return $settings;
    }
}

if (!function_exists('get_image')) {
    function get_image($path)
    {
        if (empty($path)) return '';
        return route('media.file', ['p' => basename($path)]);
    }
}

if (!function_exists('setEnvValue')) {
    function setEnvValue($key, $value)
    {
        $envFilePath = app()->environmentFilePath();
        $contents = File::get($envFilePath);

        $newValue = is_string($value) ? '"' . addslashes($value) . '"' : $value;
        $quotedKey = preg_quote($key, '/');
        $pattern = "/^{$quotedKey}=.*/m";

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, "{$key}={$newValue}", $contents);
        } else {
            $contents .= "\n{$key}={$newValue}";
        }

        File::put($envFilePath, $contents);
    }
}

if (!function_exists('setEnvValues')) {
    function setEnvValues(array $keyValuePairs)
    {
        $envFilePath = app()->environmentFilePath();
        $contents = File::get($envFilePath);

        foreach ($keyValuePairs as $key => $value) {
            $newValue = is_string($value) ? '"' . addslashes($value) . '"' : $value;
            $quotedKey = preg_quote($key, '/');
            $pattern = "/^{$quotedKey}=.*/m";

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, "{$key}={$newValue}", $contents);
            } else {
                $contents .= "\n{$key}={$newValue}";
            }
        }

        File::put($envFilePath, $contents);
    }
}

if (!function_exists('strRandom')) {
    function strRandom($length = 12)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('putPermanentEnv')) {
    function putPermanentEnv($key, $value)
    {
        $path = app()->environmentFilePath();
        $escaped = preg_quote('=' . env($key), '/');
        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$value}",
            file_get_contents($path)
        ));
    }
}

if (!function_exists('slug')) {
    function slug($title)
    {
        return \Illuminate\Support\Str::slug($title);
    }
}

if (!function_exists('productType')) {
    function productType($type)
    {
        if ($type === Status::TOPUP) {
            return "Game / Topup";
        } elseif ($type === Status::INGAME) {
            return "Game / In Game";
        } elseif ($type === Status::VOUCHER) {
            return "Game / Voucher";
        } else {
            return "Digital Product";
        }
    }
}

if (!function_exists('jsonToPlainText')) {
    function jsonToPlainText($jsonData)
    {
        $data = json_decode($jsonData, true);
        if (!is_array($data)) {
            return '';
        }
        $result = '';

        foreach ($data as $key => $value) {
            $key = ucwords(str_replace('_', ' ', $key));
            $result .= ucfirst($key) . ': ' . $value . '<br>';
        }

        return $result;
    }
}

if (!function_exists('jsonToPlainTextAdmin')) {
    function jsonToPlainTextAdmin($jsonData)
    {
        $data = json_decode($jsonData, true);
        $result = '';

        foreach ($data as $key => $value) {
            $key = ucwords(str_replace('_', ' ', $key));
            $result .= ucfirst($key) . ': ' . $value . PHP_EOL;
        }

        return $result;
    }
}

// Payment Gateway
if (!function_exists('depositRedirectUrl')) {
    function depositRedirectUrl($deposit, $gateway)
    {
        return route('user.deposit.ipn', [$deposit->track_id, $gateway]);
    }
}

if (!function_exists('depositIpnRedirectUrl')) {
    function depositIpnRedirectUrl()
    {
        return route('user.addfunds');
    }
}

if (!function_exists('depositCancelUrl')) {
    function depositCancelUrl()
    {
        return route('user.deposit.cancel');
    }
}


if (!function_exists('orderRedirectUrl')) {
    function orderRedirectUrl($order, $gateway)
    {
        return route('user.order.ipn', [$order->track_id, $gateway]);
    }
}

if (!function_exists('orderIpnRedirectUrl')) {
    function orderIpnRedirectUrl($order)
    {
        return ($order->product->isVoucher()) ? route('user.codes') : route('user.orders');
    }
}

if (!function_exists('orderCancelUrl')) {
    function orderCancelUrl($order)
    {
        return ($order->product->isVoucher()) ? route('user.code.cancel') : route('user.order.cancel');
    }
}

if (!function_exists('getPercentageAmount')) {
    function getPercentageAmount($amount, $percentage)
    {
        return amount(($amount * $percentage) / 100);
    }
}