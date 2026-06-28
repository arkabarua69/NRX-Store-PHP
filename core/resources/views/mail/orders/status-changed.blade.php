<x-mail::message>
# Order Status Updated

Your order #{{ $order->id }} status has been updated.

**Product:** {{ $order->product->title }}  
**Package:** {{ $order->variation->title }}  
**Amount:** {{ price($order->amount) }}  
**Status:** {{ strtoupper($order->status) }}

@if($order->voucher_code)
**Voucher Code:** {{ $order->voucher_code }}
@endif

@if($order->delivery_message)
**Message:** {{ $order->delivery_message }}
@endif

<x-mail::button :url="route('user.orders')">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
