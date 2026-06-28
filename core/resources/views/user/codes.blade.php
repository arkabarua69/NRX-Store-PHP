@extends('layouts.app') 
@section('title') {{ __('My Codes') }} {{ __('-') }} {{ $settings->site_title }} @endsection 
@section('content') 
<section class="orders">
  <div class="mx-auto container mx-auto text-center m-4">
    <div class="pxa-4 md:px-0">
      <div class="bg-white border rounded-lg overflow-hidden mx-auto mr-2">
        <div class="text-left px-3 flex items-center justify-between">
          <div class="flex items-center">
            <svg viewBox="0 0 24 24" class="mr-2" style="width: 24px; height: 24px">
              <path fill="currentColor" d="M11 15H17V17H11V15M9 7H7V9H9V7M11 13H17V11H11V13M11 9H17V7H11V9M9 11H7V13H9V11M21 5V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3H19C20.1 3 21 3.9 21 5M19 5H5V19H19V5M9 15H7V17H9V15Z"></path>
            </svg>
            <h2 class="text-lg text-black py-2 font-normal fb"> {{ __('My Codes') }}</h2>
          </div>
          <a href="https://shop.garena.my/app/100067/idlogin" target="_blank" class="btn theme-btn btn-sm shadow redem-btn"> {{ __('Redeem Code') }} </a>
        </div>
        <hr> @forelse ($codes as $order)
        <!--- item --->
        <div class="orders-list border-b-2 m-2">
          <div class="sm:flex">
            <div class="w-full sm:w-1/2">
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Serial NO') }}: </span> {{ $order->id }}
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Date') }}: </span> {{ custom_date($order) }}
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Package') }}: </span> @if (!empty($order->variation->title)) {{ $order->variation->title }} @endif
              </p>
            </div>
            <div class="w-full sm:w-1/2">
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Price') }}: </span> {{ price($order->amount) }}
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Status') }}: </span>
                <span class="{{ \App\Constants\OrderStatus::color($order->status) }}">
                  <span class="order-status">{{ strtolower($order->status) }}</span> @if ($order->isPending()) <a class="btn theme-btn btn-sm btn-right-mobile" style="padding: 3px 6px;" href="{{ route('user.order.pay', ['id' => $order->id]) }}">
                    {{ __('Pay Now') }}
                  </a> @endif </span>
              </p>
              <div class="w-full"> @if (!empty($order->voucher_code)) <p class="px-3 py-1 text-left">
                  <span class="font-bold">{{ __('Your Code') }}: </span>
                </p>
                <div>
                  <div style="background: rgb(241, 236, 247); margin: 0px 12px; padding: 5px 4px; border-radius: 5px; white-space: pre-line; text-align: left;">{!! implode(' <br>', explode(',', $order->voucher_code)) !!} </div>
                  <span data-text="{{ $order->voucher_code }}" id="copy">
                    <button class="align-middle text-center px-2 py-1 text-sm font-thin inline-block rounded w-38 flex items-center text-center ml-3 mt-2 code-btn copy-icon">
                      <div class="w-38 rounded h-full flex items-center icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                          <title>{{ __('Copy Code') }}</title>
                          <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z"></path>
                        </svg>
                      </div> {{ __('Copy Code') }}
                    </button>
                  </span>
                </div> @endif
              </div>
            </div>
          </div>
        </div>
        <!--- /item ---> 
        @empty 
        <div class="box-form mx-auto w-36 order-not-found">
          <h4 class="fb-normal text-base">No code found !</h4>
          <a href="{{ route('home') }}#topup" class="bg-red-500 border border-red-500 hover:bg-red-500 text-white text-xs py-1 px-2 md:px-2 rounded uppercase paglabazar-btn"> Order Now </a>
        </div> 
        @endforelse 
        @if ($codes->lastPage() > 1) <p class="p-3 text-gray-800 text-center border border-b">
          {{ $codes->links('pagination') }}
        </p> 
        @endif
      </div>
    </div>
  </div>
</section> 
@endsection 
@push('script') 
@include('scripts.user.common') 
@endpush