@extends('layouts.app')
@section('title')
{{ $product->title }} {{ __('-') }} {{ $settings->site_title }}
@endsection
@section('content')
<!--- checkout page--->
<div>
   <div class="p-2 container m-auto checkout_page">
      <div class="bg-white border rounded-md">
         <div class="flex">
            <div>
               <img class="rounded-3xl p-2 w-24 h-24" src="{{ $product->image_url }}" alt="UNIPIN VOUCHER  (BDT)">
            </div>
            <div class="flex items-center">
               <div>
                  <h2 class="text-lg capitalize">{{ $product->title }}</h2>
                  <div class="text-gray-400 text-sm text-left">
                     <span>{{ productType($product->type) }} </span>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div>
         <form method="POST" action="{{ route('user.topup.buynow') }}" class="md:flex gap-2">
            @csrf
                     <input type="hidden" name="variation_id" id="variation_id" value="">
                     <input type="hidden" name="variation_price" id="variation_price" value="">
                     @if ($settings->wallet)
                     <input type="hidden" name="payment_method" id="payment_method" value="wallet">
                     @else
                     <input type="hidden" name="payment_method" id="payment_method" value="uddoktapay">
                     @endif

            <section class="w-full md:w-2/3 mt-2">
               <div class="bg-white border rounded-md">
                  <div class="text-left px-3 flex items-center">
                     <div class="_order_header_step_circle mr-2">1</div>
                     <h2 class="text-lg text-black py-2 font-normal fb"> Select Recharge </h2>
                  </div>
                  <hr>
                  <div class="p-1 md:p-4 inline-grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 package-item-outer w-full">
                     @forelse($product->variations as $variation)
                     <button type="button" class="sm-device-package mb-0 w-full drop-shadow-2xl list-group-item flex content-between p-2 active:order-0 variation_list @if ($variation->stock < 1 || ($product->isVoucher() && count($variation->vouchers) < 1)) stockout @endif" style="font-size: 11px; position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; height: 50px;"
                        id="{{ $variation->id }}" data-price="{{ $variation->price }}">
                        <div class="w-full flex flex-wrap">
                           <div class="flex items-center">
                              <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa h-2 mr-2 w-4 text-gray-300 fa-circle fa-w-16 fa-2x">
                                 <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z" class=""></path>
                              </svg>
                              <span class="text-xs font-primary">{{ $variation->title }}</span>
                           </div>
                           @if ($variation->stock < 1 || ($product->isVoucher() && count($variation->vouchers) < 1))
                           <h6 class="bg-red-500 ml-2 rounded-full text-white px-2" style="font-size: 8px; padding-top: 3px; padding-bottom: 0px; max-width: 70px;"> STOCK OUT </h6>
                           @endif
                           <!---->
                        </div>
                        <div class="font-bold fb-normal" style="color: var( --theme-color); min-width: 46px; float: right; text-align: right;">{{ price($variation->price) }} </div>
                     </button>
                     @empty
                     <div class="text-secondary text-center not-found p-tb-20">
                        {{ __('No recharge package found to show.') }}
                     </div>
                     @endforelse
                  </div>
                  <!---->
                   <!--- tutorial ---> 
                    @if($product->has_tutorial) 
                    <div class="ml-4 mt-2 md:mt-0">
                      <div>
                        <p class="_body2 mb-3">
                          <a href=" {{$product->tutorial_link}}" target="_blank" class="text-left text-lg flex items-start info-text blink_me" style="color: rgb(0, 0, 238);">
                            <span class="text-lg flex" style="font-family: initial;">
                              {{$product->tutorial_text}}
                            </span>
                            <svg stroke="currentColor" fill="none" stroke-width="0" viewBox="0 0 24 24" height="22" width="22" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                          </a>
                        </p>
                      </div>
                    </div> 
                    @endif
                    <!--- /tutorial --->


                </div>

            </section>
            <div class="w-full md:w-1/3 mt-2">
               @if ($product->isTopup() || $product->isInGame())
               <!---account info --->
               <section>
                  <div class="border bg-white rounded-md">
                     <div class="text-left px-3 flex items-center">
                        <div class="_order_header_step_circle mr-2">2</div>
                        <h2 class="text-lg text-black py-2 font-bold fb-normal"> {{ __('Account Info') }} </h2>
                     </div>
                     <hr>
                     @if ($product->isTopup())
                     <!--- player id --->
                     <div class="p-3">
                        <div class="relative">
                           <label class="label-title">Player Id</label><input name="account_info[player_id]" id="player_id" type="text" placeholder="Player ID (UID)" class="form-input relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0 rounded-md placeholder-gray-400 dark:placeholder-gray-500 text-sm px-2.5 py-2.5 shadow-sm bg-transparent text-gray-900 dark:text-white ring-1 ring-inset dark:ring-black-900 focus:ring-2 focus:ring-black-900 dark:focus:ring-black-900 player_id checkId" required>
                           <span id="player_id_error"></span>
                           <div class="idExist_container">
                              <span class="idExist"></span>
                           </div>
                        </div>
                     </div>
                     <!--- /player id --->
                     @endif
                     @if ($product->isInGame())
                     <!--- /account --->
                     <div class="p-3">
                        <div class="p-2 pb-0">
                           <label>{{ __('Account Type') }}</label>
                           <select  name="account_info[account_type]" id="game_account_type" class="form-select relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0 rounded-md text-sm px-3.5 py-2.5 shadow-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 pe-11">
                              <option value="Gmail">Gmail</option>
                              <option value="Facebook">Facebook</option>
                           </select>
                        </div>
                        <div class="p-2 pb-0"><label>Enter Email/Number</label><input name="account_info[game_account]" id="game_account" type="text" placeholder="Enter Email/Number" class="form-input relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0 rounded-md placeholder-gray-400 dark:placeholder-gray-500 text-sm px-2.5 py-2.5 shadow-sm bg-transparent text-gray-900 dark:text-white ring-1 ring-inset dark:ring-black-900 focus:ring-2 focus:ring-black-900 dark:focus:ring-black-900" required></div>
                        <div class="p-2 pb-0"><label>Password</label><input name="account_info[game_password]" id="game_password" type="password" placeholder="Enter Password" class="form-input relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0 rounded-md placeholder-gray-400 dark:placeholder-gray-500 text-sm px-2.5 py-2.5 shadow-sm bg-transparent text-gray-900 dark:text-white ring-1 ring-inset dark:ring-black-900 focus:ring-2 focus:ring-black-900 dark:focus:ring-black-900" required></div>
                        <div class="p-2 pb-0"><label>Account Back Up If Have</label><input name="account_info[game_backup]" id="game_backup" type="text" placeholder="Enter Back Up Code" class="form-input relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0 rounded-md placeholder-gray-400 dark:placeholder-gray-500 text-sm px-2.5 py-2.5 shadow-sm bg-transparent text-gray-900 dark:text-white ring-1 ring-inset dark:ring-black-900 focus:ring-2 focus:ring-black-900 dark:focus:ring-black-900"></div>
                        @if (!empty($settings->backup_code_video_link))
                        <div class="w-full text-left ml-1">
                           <!----><a href="{{ $settings->backup_code_video_link }}" target="_blanck" class="text-left text-lg flex items-start info-text" style="color: rgb(0, 0, 238);">কিভাবে ফেসবুক অ্যাকাউন্ট এর ব্যাকআপ কোড বের করবেন?</a>
                        </div>
                        @endif
                     </div>
                     @endif
                     <!--- /account --->
                  </div>
               </section>
               <!---/account info --->
               @endif
               @if ($product->isVoucher())
               <!--- quantity --->
               <section>
                  <div class="flex justify-between align-middle px-3 bg-white rounded-md border quantity-container">
                     <div class="my-auto font-primary"> {{ __('Quantity') }} </div>
                     <div>
                        <label for="{{ __('Quantity') }}" class="sr-only"> {{ __('Quantity') }} </label>
                        <div class="flex items-center border-2 my-2 border-gray-200 rounded-full px-2 quantity-options">
                           <div class="cursor-pointer w-6 h-6 flex items-center justify-center" id="decrease">
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="h-6 w-6">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
                              </svg>
                           </div>
                           <input value="1" min="1" autocomplete="off" type="number" class="h-10 w-16 border-transparent text-center bg-white [&amp;::-webkit-inner-spin-button]:appearance-none" id="quantity" name="quantity">
                           <div class="cursor-pointer w-6 h-6 flex items-center justify-center" id="increase">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                              </svg>
                           </div>
                        </div>
                     </div>
                  </div>
               </section>
               <!--- /quantity --->
               @endif
               <section>
                  <div class="bg-white border mt-2" style="border-radius: 0.375rem; border-width: 1px;">
                     <div class="text-left px-3 flex items-center">
                        <div class="_order_header_step_circle mr-2">3</div>
                        <h2 class="text-lg text-black py-2 font-bold fb-normal"> Payment Methods </h2>
                     </div>
                     <hr>
                     <div class="flex justify-center py-3 px-2">
               @if ($settings->wallet)
                        <div class="w-full pm_list" id="wallet">
                           <div class="m-1">
                              <label for="wallet" class="mb-0 w-full list-group-item pt-2 cursor-pointer" style="display: block; font-size: 11px; position: relative; overflow: hidden;">
                                 <span class="absolute left-0 check_selected element-check-label" style="color: rgb(255, 255, 255);">L </span>
                                 <img src="{{ asset('assets/template/images/wallet.png') }}" alt="wallet" class="p-2" style="height: 6rem;">
                                 <input id="wallet" name="send" type="radio" class="absolute" value="1" style="visibility: hidden;">
                                 <div class="bg-gray-300 text-left p-1">
                                    <p class="text-xs p-0 capitalize fb-normal"> {{ __('Wallet Pay') }}
                        @auth
                        {{ __('(') }}{{ $settings->currency_symbol }}<span
                           id='wallet_balance'>{{ amount(auth()->user()->balance) }}</span>
                        {{ __(')') }}
                        @endauth</p>
                                 </div>
                              </label>
                           </div>
                        </div>

               @endif

                        <div class="text-center w-full pm_list" id="payment_gateway">
                           <div class="m-1">
                              <label for="sslcom" class="mb-0 w-full list-group-item pt-2 cursor-pointer" style="display: block; font-size: 11px; position: relative; overflow: hidden;">
                                 <span class="absolute check_selected left-0" style="color: rgb(255, 255, 255);">L </span>
                                 <img src="{{ asset('assets/template/images/bd_payments.png') }}" alt="SSL" class="p-2" style="height: 6rem;">
                                 <input id="sslcom" name="send" type="radio" class="absolute" value="2" style="visibility: hidden;">
                                 <div class="bg-gray-300 text-left">
                                    <p class="text-xs p-1 capitalize fb-normal"> {{ __('Instant Pay') }}</p>
                                 </div>
                              </label>
                           </div>
                        </div>
                     </div>
                     <div class="row pb-5">
                        <!---->
                        <div class="col-md-12 text-left px-3">
                           <div>
                              <div class="fb-normal text-xs flex items-center" style="color: gray;">
                                 <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa h-4 w-4 mr-1 fa-info-circle fa-w-16 fa-2x">
                                    <path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 448c-110.532 0-200-89.431-200-200 0-110.495 89.472-200 200-200 110.491 0 200 89.471 200 200 0 110.53-89.431 200-200 200zm0-338c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
                                 </svg>
                                 আপনার অ্যাকাউন্ট ব্যালেন্স 
                                 <div style="min-width: 100px;">
                                    <span class="flex items-center">
                                       <p class="pl-2 text-pink-500 font-bold fb cost_alert_bl"> 
                                            {{ $settings->currency_symbol }}
                                          @auth
                                            {{ amount(auth()->user()->balance) }}
                                            @else
                                            0
                                            @endauth
                                         </p>
                                       <div class="border ml-2 p-1 rounded cursor-pointer">
                                          <svg viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                             <path fill="currentColor" d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"></path>
                                          </svg>
                                       </div>
                                    </span>
                                 </div>
                              </div>
                              <p class="fb-normal text-xs flex items-center mb-3" style="color: gray;">
                                 <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa h-4 w-4 mr-1 fa-info-circle fa-w-16 fa-2x">
                                    <path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 448c-110.532 0-200-89.431-200-200 0-110.495 89.472-200 200-200 110.491 0 200 89.471 200 200 0 110.53-89.431 200-200 200zm0-338c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
                                 </svg>
                                 প্রোডাক্ট কিনতে আপনার প্রয়োজন <span class="text-pink-500 font-bold fb" style="padding: 0px 4px; font-size: 14px;"> ৳<span id="total_cost">0</span></span> ।
                              </p>
                              <div>
                                 @auth
                                 <a href="{{ route('user.addfunds') }}" class="align-middle bg-pink-500 hover:bg-pink-400 text-center px-4 py-2 text-white text-sm font-semibold rounded inline-block shadow-lg w-full gosizi-btn" id="add_fund" style="margin-bottom: 10px;display:none;">
                                      ADD FUND
                                  </a>
                                 <button class="align-middle bg-pink-500 hover:bg-pink-400 text-center px-4 py-2 text-white text-sm font-semibold rounded inline-block shadow-lg w-full gosizi-btn buy_now_btn" id="buy_now" type="submit" disabled> {{ __('BUY NOW') }} </button>
                                 @else
                                 <a href="/login">
                                 <button class="align-middle bg-pink-500 hover:bg-pink-400 text-center px-4 py-2 text-white text-sm font-semibold rounded inline-block shadow-lg w-full gosizi-btn checkout_login" type="button"> {{ __('LOG IN') }} </button>
                                 </a>
                                 @endauth
                              </div>
                           </div>
                        </div>
                        <!---->
                     </div>
                  </div>
               </section>
            </div>
         </form>
      </div>
      <div class="mt-2 bg-white border rounded-md">
         <h1 class="font-bold p-2"> Rules &amp; Conditions </h1>
         <hr>
         <div class="p-2">
            {!! $product->content !!}
         </div>
      </div>
   </div>
</div>
<!--- /checkout page--->

@endsection

@push('script')
@include('scripts.checkout')
@if ($settings->enable_uid_checker)
@if ($product->uid_checker == 1)
<script>
   $(document).ready(function() {
       
       $('.idExist_container').click(function() {
       $('.player_id').focus();
        });
   
   
       $('.player_id').on('focus', function() {
           $('.idExist_container').hide();
       });
   
       $('.checkId').on('focusout', function() {
           $('.idExist_container').show();
           
           var id = $(this).val().trim();
   
           if (!id) {
               $('.idExist').hide();
               return;
           }
   
           $('.idExist').html('<span class="heading_title loading-container"><div class="spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span> </div></span>')
               .show();
   
           var url = "{{ route('LaravelAddons::uidcheck') }}";
   
           $.post(url, {
                   id: id,
                   _token: "{{ csrf_token() }}"
               })
               .done(function(response) {
                   const data = typeof response === 'string' ? JSON.parse(response) : response;
                   if (data.nickname) {
                       const escaped = $('<span>').text(data.nickname).html();
                       $('.idExist').html(
                           '<span class="heading_title"></span> <span class="text-success">' +
                           escaped + '</span>').show();
                   } else {
                       $('.idExist').hide();
                   }
               });
       });
   });
</script>
@endif
@endif


@endpush