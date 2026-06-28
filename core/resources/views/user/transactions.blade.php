@extends('layouts.app')

@section('title')
    {{ __('My Transactions') }} {{ __('-') }} {{ $settings->site_title }}
@endsection

@section('content')
    <section class="transactions">
  <div class="mx-auto container mx-auto text-center m-4">
    <div class="pxa-4 md:px-0">
      <div class="bg-white border rounded-lg overflow-hidden mx-auto mr-2">
        <div class="text-left px-3 flex items-center justify-between">
          <div class="flex items-center">
            <svg viewBox="0 0 24 24" class="mr-2" style="width: 24px; height: 24px">
              <path fill="currentColor" d="M11 15H17V17H11V15M9 7H7V9H9V7M11 13H17V11H11V13M11 9H17V7H11V9M9 11H7V13H9V11M21 5V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3H19C20.1 3 21 3.9 21 5M19 5H5V19H19V5M9 15H7V17H9V15Z"></path>
            </svg>
            <h2 class="text-lg text-black py-2 font-normal fb"> {{ __('My Transactions') }}</h2>
          </div>
        </div>
          <hr>
       @forelse ($transactions as $transaction)
        <!--- item --->
        <div class="transactions-list border-b-2 m-2">
          <div class="sm:flex">
            <div class="w-full sm:w-1/2">
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Date') }}: </span> {{ custom_date($transaction) }}
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Payment Method') }}: </span> {{ strtoupper($transaction->payment_method) }}
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Transaction ID') }}: </span> {{ strtoupper($transaction->transaction_id) }}
              </p>

            </div>
            <div class="w-full sm:w-1/2">
    
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Amount') }}: </span> 
                <span class="@if ($transaction->isCredit()) {{ 'text-danger' }} @else {{ 'text-success' }} @endif">{{ price($transaction->amount) }}</span>
              </p>
              <p class="px-3 py-1 text-left">
                <span class="font-bold">{{ __('Remarks') }}: </span> {{ $transaction->remarks }}
              </p>
          </div>
        </div>
        </div>
        <!--- /item ---> 
        @empty <div class="box-form mx-auto w-36 transactions-not-found">
          <h4 class="fb-normal text-base">No transaction found!</h4>
        </div> 
        @endforelse 
        @if ($transactions->lastPage() > 1) 
        <p class="p-3 text-gray-800 text-center border border-b">
          {{ $transactions->links('pagination') }}
        </p> 
        @endif
      </div>
    </div>
  </div>
</section> 
@endsection
