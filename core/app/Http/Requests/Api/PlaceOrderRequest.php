<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isReseller() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'variation_id' => 'required|exists:variations,id',
            'quantity' => 'integer|min:1|max:10',
            'account_info' => 'sometimes|array',
        ];
    }
}
