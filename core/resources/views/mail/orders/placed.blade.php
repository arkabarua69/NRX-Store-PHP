<x-mail::message>
# A New Order Placed

Id: {{ $order->id }} <br> 
Product: {{ $order->product->title }} <br> 
Package: {{ $order->variation->title }} <br> 
Cost: {{ price($order->amount) }} <br> 
Current Status: {{ strtoupper($order->status) }} <br> 

Order by: {{ $order->user->name }} <br> 
Ordered at: {{ $order->created_at->format('d-m-Y H:i A') }} <br> 

# Order Note

@foreach ($order->account_info as $key => $value)
    {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach

Thanks, <br> 
{{ config('app.name') }}
</x-mail::message>
