<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Site Title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $settings->seo_description }}" />
    <meta name="keywords" content="{{ $settings->seo_keywords }}" />

    @if (!empty($settings->favicon))
        {{-- Favicon --}}
        <link rel="shortcut icon" type="image/png" href="{{ get_image($settings->favicon) }}">
    @endif

    @if ($settings->enable_pwa)
        @PWA
    @endif

    {{-- Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Site Title')">
    <meta property="og:description" content="{{ $settings->seo_description }}">
    @if (!empty($settings->fb_og_image))
        <meta property="og:image" content="{{ get_image($settings->fb_og_image) }}">
    @endif

    {{-- Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Site Title')">
    <meta property="twitter:description" content="{{ $settings->seo_description }}">
    @if (!empty($settings->twitter_og_image))
        <meta property="twitter:image" content="{{ get_image($settings->twitter_og_image) }}">
    @endif

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('assets/template/css/bootstrap/bootstrap.min.css') }}">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('assets/template/fonts/fontawesome/css/all.min.css') }}">

    {{-- Toastr --}}
    <link rel="stylesheet" href="{{ asset('assets/template/js/toastr/toastr.min.css') }}">

    {{-- Custom --}}
    <link rel="stylesheet" href="{{ asset('assets/template/css/styles.css') }}?<?=rand(0000,9999)?>">

    <style>
        :root {
            --theme-color: {{ $settings->theme_color }};
            --logo-color: {{ $settings->logo_color }};
            --background-color: {{ $settings->background_color }};
            --primary-font-color: {{ $settings->font_color }};
            --navigation-background-color: {{ $settings->navigation_background_color }};
            --navigation-font-color: {{ $settings->navigation_font_color }};
            --footer-color: {{ $settings->footer_color }};
            --footer-font-color: {{ $settings->footer_font_color }};
            --content-box-color: {{ $settings->content_box_color }};

            @if ($settings->background_image)
                --background-image: linear-gradient(350deg, rgb(244, 249, 255), rgba(237, 244, 255, 0.79)),
                    url(data:image/png;base64,UklGRtQEAABXRUJQVlA4TMgEAAAvj8FjAAVFA4Bqa9f7P14MQ4yIiBgSsRgW316rZvat/3ZOEf135LaNJGVOc3Nplk5c8QfO8avRaXkRWzSJMwnO2STKiUVTMWJoE02L4ldsGHwygXeHpUKw1OFMGJK3gS8r5EeuWk4KaKnosum7bvrOZhcthXAYqjpaIL8GxVvYZiz66o7BwCiFkAxsgV03/LowWxj5Am15/DrU00fDYaTpjyo+n9GrATIcB7BecDyzXnCHJoaPLPqzQl8cvwrlcaKgO47Eh1gG5XEaxodgr/wzyA/BHbLyL8s/2EVUfjEL8ENwVGIID8EZ6sL4NSaoN1KxzkgcLQHJlRSWNjtQZ7pRzS7WPjtmS0gXKZ0wzQXX6PKNFPFccMErVSFIbYqhxhvVCsRtUfzIB5a7vkSTr18DSoPjcfRuIhipDAQXwsn31lFsA/WRJoIzUrlzkUJIE8E+8oBzImhjwUBpIJiDp0Xxa9MO3zREOJvgWU0Fm4aIRibB1EyC/wnmx+SmBbENnsY4Lz944nJQCA4DwRnnhfm4nzrAtFzgwdPYzwvjQHBUCCZeDb+OxDKfnTIkuTWdiimp3lrZ7pHnM8JRbq1sgVgVguO1lRXCohDM6Put6VQI7rdWVhLkOQZhwr4UflVrpAldZivtUFaBDUOZxItgrfvQIP4owyiGUY+hake9y4xISsHF8UL4HcAGG9XqPQBbvAeD/SRQTB4KSAODh5Isxi7DsQ5+lG3e3GHy5sI7eozB5DEeRo+R1sFvHR78Ovit445qHfyyDYNYit/1Dre87g73l5Ufr5/fu/6/8Ae+WQc/EMtJjc9f957PX2d8/hbTd30d/F7Yn8b37E/Ty/rTTOvg16Fa/nrS6+a3Ypjf0DS/8Uvnt3XwO9mxUm8w+RvN6G/Aj9aISg4d42MqMPobzeRvBJ3gC7qF8DsbUpW53AxXqQmS0f9TCC5IcntYdJWNZ/D/xOj/JUjXDxWyQnAlbOdS+J1SvMkfj6Dwx7MYjHU38seTxh9PI3/cWfzxrPDHweaP+yLnMvit4z7vX2B+PvCoFyI07K/VSE6/+Caa/bXUBuqTZn9N9GtojmI17K8hjXoiDn5R/I5WPNbrlbKLtU03MdOtW+2IZbZtKW243xm4yUTwbL+zTwWH4X5nkykGxH7rSNN0a7TV6NJFZkX/g2Ed/JSX2OLpsOw/O8v+80m+6wTf959FN815Oi37z86y/3yQl8HV+Lr4nf1HD+m9h4jSPy4f4LqgAQNdcg6L43dWKCgGHzX7l+VnYnxZfsZnMgjGAvVcHr+TTBcN/TPv1LtBEzs6l8HvZXc7mCxVX3lHVU13VGi8U18Hv9+cO/X/cOTet/+593nuffufez++Mb/tf+5dmXvf/ufe65flt/3PvT819779z72/f+59+597H+fet/+591Huffufe3efwW/7n3v/dz33vv3PvY9y79v/3DuOa/ufe3cfwG/7n3v349r+5963/7n3NeTet/+593vuffufe/+Y3Pv2P/cOGgzb/9z79j/3XpaRey/btNz79j/3Dg8M2//cO8wEb/9z72+fe9/+595Bh2H7n3vf/ufev3ruffufex/k3rf/ufcPzL1v/3Pvv2659+1/7n1Fufftf+59+597PwE=);
            @endif
        }
    </style>

    <style>
        @if (!$settings->footer_menu)
            .sticky-footer-container {
                display: none;
            }

            body {
                margin-bottom: 0px;
            }
        @endif
    </style>

    @stack('style')


    {{-- extra --}}
    <link rel="stylesheet" href="{{ asset('assets/template/css/tailwindcss.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/template/css/custom-styles.css') }}?<?=rand(0000,9999)?>"> 

    {!! $settings->header_tags !!}
</head>

<body>
<div class="body-bg">

{{-- Navigation --}}

<div class="header">
  <div class="container m-auto p-2 py-3 md:py-5 md:px-0">
    <nav class="flex items-center justify-between">
      <a href="{{ route('home') }}" class="">
        <img src="{{ get_image($settings->logo) }}"  alt="{{ $settings->site_name }}" data-nuxt-img="" srcset="{{ get_image($settings->logo) }} 1x, {{ get_image($settings->logo) }} 2x" class="w-28 md:w-48 logo">
      </a>
      <div class="relative">
        <div class="flex items-center">
          <nav class="text-left hidden md:block">
            <div class="w-full flex-grow flex items-center lg:w-auto">
              <div class="text-sm flex-grow animated jackinthebox mx-auto">
                <a href="{{ route('home') }}#topup" class="block inline-block text-md font-bold mx-2 p-1 rounded-lg fb-normal link"> Topup </a>
                <a href="{{ route('page', ['slug' => 'contact-us']) }}" class="block inline-block text-md font-bold mx-2 p-1 rounded-lg fb-normal link"> Contact Us </a>
              </div>
            </div>
          </nav>
            @auth
                    <button class="flex text-xs md:px-4 py-1.5 border rounded-full text-black top-profile font-primary">
                      <div class="relative">
                        <div class="flex items-center cursor-pointer px-2 duration-75">
                          <div class="mr-2"></div>
                          <img src="https://ui-avatars.com/api/?name={{ strtoupper(substr(auth()->user()->name, 0, 1)) }}&size=96&background=f29f2c" class="mr-1">
                          <p class="_body2 text-gray-500 capitalize select-none user-child name_user">{{ Str::limit(auth()->user()->name, 12) }}</p>
                          <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="1.5em" width="1.5em" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 duration-150 user-child">
                            <path d="M16.293 9.293 12 13.586 7.707 9.293l-1.414 1.414L12 16.414l5.707-5.707z"></path>
                          </svg>
                        </div>
                      </div>
                    </button>

            @else
          <div class="flex items-center">
            @if ($settings->enable_pwa)
            <button id="pwa-install-btn" class="hidden flex items-center px-4 py-1.5 border rounded-full text-black mr-2 font-primary">
              <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="1.2em" width="1.2em" xmlns="http://www.w3.org/2000/svg" class="mr-1">
                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path>
              </svg>
              <span>Install App</span>
            </button>
            @endif
            <a href="{{ route('login') }}" class="router-link-active router-link-exact-active btn-pro btn-pro-login rounded mx-2 border-2 text-primary-500 border-primary-500" aria-current="page"> {{ __('LOGIN') }} </a>
            <a href="{{ route('register') }}" class="btn-pro  btn-register rounded ml-2 border-2 border-primary-500 bg-primary-500 text-white"> {{ __('REGISTER') }} </a>
          </div>
            @endauth
        </div>
        

            @auth
                  <div id="userMenu" class="hidden bg-white rounded shadow-md absolute mt-12 top-0 right-0 min-w-full overflow-auto z-30">
                    <nav class="flex fixed items-center justify-between h-16 bg-white text-gray-700 border-b border-gray-200 z-10 gosizi-navlist" style="position: fixed; bottom: 0px;">
                      <div class="z-10 fixed inset-0 transition-opacity">
                        <div tabindex="0" class="absolute inset-0 bg-black opacity-50 nav-overlay"></div>
                      </div>
                      <aside class="transform top-0 right-0 w-64 bg-white fixed h-full overflow-auto ease-in-out transition-all duration-300 z-30 translate-x-0">
                        <button id="userButton" class="flex items-center focus:outline-none p-3">
                          <img src="https://ui-avatars.com/api/?name={{ strtoupper(substr(auth()->user()->name, 0, 1)) }}&size=96&background=f29f2c" backgroundcolor="#D81C4B" color="#fff" style="height: 50px;">

                          <div>
                            <div class="text-left w-full">
                              <span class="px-3 font-normal font-primary">{{ __('Hi,') }} 
                                {{ strtoupper(substr(auth()->user()->name, 0, 12)) }}<?=strlen(auth()->user()->name)>12?'...':''?></span>
                            </div>
                            <div class="text-left">
                              <span class="px-3">{{auth()->user()->email}}</span>
                            </div>
                          </div>
                        </button>
                        <div class="w-full mx-auto text-center">
                        <a href="{{ route('logout') }}" class="inline-block">
                          <button type="button" class="align-middle bg-pink-500 rounded-full mx-auto text-center hover:bg-pink-400 text-center px-1 py-2 text-white text-sm font-semibold rounded-lg inline-block shadow-lg px-6 mb-2 d-block btn-primary gosizi-btn">
                            <!---->
                            <span class="flex items-center justify-center p-0">
                              <span class="mr-2">
                                <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="power-off" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width: 0.83rem;">
                                  <path fill="currentColor" d="M388.5 46.3C457.9 90.3 504 167.8 504 256c0 136.8-110.8 247.7-247.5 248C120 504.3 8.2 393 8 256.4 7.9 168 54 90.3 123.5 46.3c5.8-3.7 13.5-1.8 16.9 4.2l11.8 20.9c3.1 5.5 1.4 12.5-3.9 15.9C92.8 122.9 56 185.1 56 256c0 110.5 89.5 200 200 200s200-89.5 200-200c0-70.9-36.8-133.1-92.3-168.6-5.3-3.4-7-10.4-3.9-15.9l11.8-20.9c3.3-6.1 11.1-7.9 16.9-4.3zM280 276V12c0-6.6-5.4-12-12-12h-24c-6.6 0-12 5.4-12 12v264c0 6.6 5.4 12 12 12h24c6.6 0 12-5.4 12-12z"></path>
                                </svg>
                              </span>
                              <span class="no-underline text-xs">Logout</span>
                            </span>
                            <!---->
                          </button>
                      </a>
                        </div>
                        <hr>
                        <a href="{{ route('user.account') }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6">
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                              </svg>
                            </span> {{ __('My Account') }} </span>
                        </a>
                        <a href="{{ route('user.orders') }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6">
                                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                              </svg>
                            </span> {{ __('My Orders') }} </span>
                        </a>
                        <a href="{{ route('user.codes') }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                              </svg>
                            </span>
                            <span> {{ __('My Codes') }} </span>
                          </span>
                        </a>
                        <a href="{{ route('user.transactions') }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                              </svg>
                            </span> {{ __('My Transactions') }} </span>
                        </a>
                    @if ($settings->wallet)
                        <a href="{{ route('user.addfunds') }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg class="w-6 h-6" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M3 0V3H0V5H3V8H5V5H8V3H5V0H3M10 3V5H19V7H13C11.9 7 11 7.9 11 9V15C11 16.1 11.9 17 13 17H19V19H5V10H3V19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V16.72C21.59 16.37 22 15.74 22 15V9C22 8.26 21.59 7.63 21 7.28V5C21 3.9 20.1 3 19 3H10M13 9H20V15H13V9M16 10.5A1.5 1.5 0 0 0 14.5 12A1.5 1.5 0 0 0 16 13.5A1.5 1.5 0 0 0 17.5 12A1.5 1.5 0 0 0 16 10.5Z"></path>
                              </svg>
                            </span> {{ __('Add Fund') }} </span>
                        </a>
                    @endif
                        <a href="{{ route('page', ['slug' => 'contact-us']) }}" class="text-gray-900 no-underline">
                          <span class="flex items-center p-4 font-primary">
                            <span class="mr-2">
                              <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                            </span>
                            <span> Contact Us </span>
                          </span>
                        </a>
                        <hr>
                        <div class="w-full mx-auto text-center mt-3">
                          <a href="https://wa.me/{{ $settings->whatsapp_number }}" target="_blank" class="align-middle bg-pink-500 rounded-full mx-auto text-center hover:bg-pink-400 text-center px-1 py-2 text-white text-sm font-semibold rounded-lg inline-block shadow-lg w-32 px-6 mb-2 d-block btn-primary gosizi-btn">
                            <span class="flex items-center justify-center">
                              <span class="mr-2">
                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="20" width="20" xmlns="http://www.w3.org/2000/svg">
                                  <path d="M12 2C6.486 2 2 6.486 2 12v4.143C2 17.167 2.897 18 4 18h1a1 1 0 0 0 1-1v-5.143a1 1 0 0 0-1-1h-.908C4.648 6.987 7.978 4 12 4s7.352 2.987 7.908 6.857H19a1 1 0 0 0-1 1V18c0 1.103-.897 2-2 2h-2v-1h-4v3h6c2.206 0 4-1.794 4-4 1.103 0 2-.833 2-1.857V12c0-5.514-4.486-10-10-10z"></path>
                                </svg>
                              </span>
                              <span class="no-underline">Support</span>
                            </span>
                          </a>
                        </div>
                      </aside>
                    </nav>
                  </div>
            @endauth
        <!---->
      </div>
    </nav>
  </div>
</div>

    {{-- Navigation End --}}

    @yield('content')


    {{-- Footer Start --}}
 <footer data-v-4c1ace0e="" class="mb-16 md:mb-0 text-gray-200 border-t-2 footer-bg">
            <section data-v-4c1ace0e="" class="container mx-auto pb-8">
              <div data-v-4c1ace0e="">
                <div data-v-4c1ace0e="" class="m-auto flex flex-wrap">
                  <div data-v-4c1ace0e="" class="w-full md:w-4/6 m-auto flex flex-wrap my-0">
                    <div data-v-4c1ace0e="" class="w-full md:w-1/3 px-5 md:px-0">
                      <div data-v-4c1ace0e="" class="text-lg fb mt-10 uppercase text-white font-normal tracking-wider footer-title">{{ __('STAY CONNECTED') }}</div>
                      <div data-v-4c1ace0e="" class="m-auto flex flex-wrap ff-bf mt-2 footer_nav">
                        <div data-v-4c1ace0e="" class="w-full mt-1 md:mt-2">
                          <a data-v-4c1ace0e="" class="flex ff-bf ffont-medium" href="/#" style="line-height: 17px;">
                            <span data-v-4c1ace0e="" class="text-xs">
                              <p class="footer_description">কোন সমস্যায় পড়লে হোয়াটসঅ্যাপ এ যোগাযোগ করবেন। তাহলে দ্রুত সমাধান পেয়ে যাবেন।</p>
                            </span>
                          </a>
                          <div data-v-4c1ace0e="" class="mt-1 md:mt-2">
                            <div data-v-4c1ace0e="" class="flex flex-wrap">

                             @if (!empty($settings->facebook_link))    
                              <div data-v-4c1ace0e="" class="social_icon mx-2 my-3 ml-0" data-aos="zoom-in" data-aos-duration="500">
                                <a data-v-4c1ace0e="" href="{{ $settings->facebook_link }}" target="_blank" aria-label="Social Icon" rel="noopener">
                                  <svg data-v-4c1ace0e="" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                    <path data-v-4c1ace0e="" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                  </svg>
                                </a>
                              </div>
                                @endif

                @if (!empty($settings->messenger_link))
                              <div data-v-4c1ace0e="" class="social_icon mx-2 my-3" data-aos="zoom-in" data-aos-duration="500">
                                <a data-v-4c1ace0e="" href="{{ $settings->messenger_link }}" target="_blank" aria-label="Social Icon" rel="noopener">
                                  <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                  <path d="M12 2C6.48 2 2 6.19 2 11.5c0 3.67 2.14 6.85 5.37 8.44V22l4.02-2.21c.86.21 1.75.32 2.61.32 5.52 0 10-4.19 10-9.5S17.52 2 12 2zm2.92 9.59l-3.14 3.33-2.25-2.4-5.51 5.87L9.64 8.91l3.14-3.33 2.25 2.4 5.51-5.87L14.92 11.59z"></path>
                                </svg>

                                </a>
                              </div>
                @endif


                @if (!empty($settings->youtube_link))     
                              <div data-v-4c1ace0e="" class="social_icon mx-2 my-3" data-aos="zoom-in" data-aos-duration="500">
                                <a data-v-4c1ace0e="" href="{{ $settings->youtube_link }}" target="_blank" aria-label="Social Icon" rel="noopener">
                                  <svg data-v-4c1ace0e="" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                    <path data-v-4c1ace0e="" d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                    <polygon data-v-4c1ace0e="" points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                                  </svg>
                                </a>
                              </div>
                @endif
                @if (!empty($settings->email_address))
                              <div data-v-4c1ace0e="" class="social_icon mx-2 my-3" data-aos="zoom-in" data-aos-duration="500">
                                <a data-v-4c1ace0e="" href="mailto:{{ $settings->email_address }}" target="_blank" aria-label="Social Icon" rel="noopener">
                                  <svg data-v-4c1ace0e="" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                    <path data-v-4c1ace0e="" d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline data-v-4c1ace0e="" points="22,6 12,13 2,6"></polyline>
                                  </svg>
                                </a>
                              </div>
                @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="w-full md:w-2/3 md:text-center ff-bf ffont-medium md:pl-20" data-v-4c1ace0e="">
                      <div class="p-5 w-full footer_nav" data-v-4c1ace0e="">
                        <div class="text-lg fb mt-5 uppercase text-white font-normal tracking-wider footer-title" data-v-4c1ace0e="">{{ __('OUR MOBILE APP') }}</div>
                        <div class="m-auto flex flex-wrap ff-bf mt-2 md:text-center" data-v-4c1ace0e="">
                          <div class="w-full" data-v-4c1ace0e="">
                            <div class="mt-2 md:mt-4" data-v-4c1ace0e="">
                              <a class="flex" target="_blank" href="https://play.google.com/store/apps/details?id=com.gamesbazar.topup" data-v-4c1ace0e="">
                                <svg class="md:mx-auto" width="220" height="70" fill="none" xmlns="http://www.w3.org/2000/svg" data-v-4c1ace0e="">
                                  <path d="M208 0H8a8 8 0 00-8 8v48a8 8 0 008 8h200a8 8 0 008-8V8a8 8 0 00-8-8z" fill="#000" data-v-4c1ace0e=""></path>
                                  <path d="M208 1.28A6.72 6.72 0 01214.72 8v48a6.72 6.72 0 01-6.72 6.72H8A6.72 6.72 0 011.28 56V8A6.72 6.72 0 018 1.28h200zM208 0H8a8 8 0 00-8 8v48a8 8 0 008 8h200a8 8 0 008-8V8a8.001 8.001 0 00-8-8z" fill="#A6A6A6" data-v-4c1ace0e=""></path>
                                  <path d="M109.024 34.8a6.798 6.798 0 00-4.765 11.62 6.799 6.799 0 0011.597-4.82 6.707 6.707 0 00-4.205-6.318 6.699 6.699 0 00-2.627-.482zm0 10.928a4.127 4.127 0 113.84-4.128 3.935 3.935 0 01-3.84 4.128zM94.128 34.8a6.8 6.8 0 106.816 6.8 6.707 6.707 0 00-4.205-6.318 6.702 6.702 0 00-2.627-.482h.016zm0 10.928a4.128 4.128 0 113.824-4.128 3.935 3.935 0 01-3.84 4.128h.016zm-17.744-8.832v2.88h6.912a6.032 6.032 0 01-1.6 3.632 7.074 7.074 0 01-5.328 2.112 7.68 7.68 0 010-15.36 7.36 7.36 0 015.232 2.064l2.032-2.032a10.064 10.064 0 00-7.248-2.992 10.576 10.576 0 100 21.136 9.601 9.601 0 007.376-2.96 9.6 9.6 0 002.496-6.752 9.386 9.386 0 00-.16-1.808l-9.712.08zm72.496 2.24a6.399 6.399 0 00-5.824-4.336 6.4 6.4 0 00-6.4 6.8 6.663 6.663 0 004.142 6.308 6.654 6.654 0 002.61.492 6.77 6.77 0 005.664-3.008l-2.32-1.6a3.88 3.88 0 01-3.344 1.888 3.457 3.457 0 01-3.296-2.064l9.104-3.76-.336-.72zm-9.28 2.272a3.732 3.732 0 012.122-3.612 3.736 3.736 0 011.43-.356 2.639 2.639 0 012.528 1.44l-6.08 2.528zM132.208 48h2.992V28h-2.992v20zm-4.896-11.68h-.112a4.796 4.796 0 00-3.584-1.6 6.818 6.818 0 00-6.486 6.808 6.818 6.818 0 006.486 6.808 4.645 4.645 0 003.584-1.6h.096v.976c0 2.608-1.392 4-3.632 4a3.762 3.762 0 01-3.424-2.416l-2.608 1.088a6.48 6.48 0 006.032 4.016c3.504 0 6.4-2.064 6.4-7.088V35.2h-2.752v1.12zm-3.424 9.408a4.142 4.142 0 01-3.78-4.128 4.143 4.143 0 013.78-4.128 3.834 3.834 0 012.686 1.298 3.825 3.825 0 01.946 2.83 3.797 3.797 0 01-.941 2.844 3.81 3.81 0 01-2.707 1.284h.016zM162.896 28h-7.152v20h2.992v-7.584h4.176a6.221 6.221 0 006.153-3.723 6.228 6.228 0 00-1.467-7.041A6.209 6.209 0 00162.912 28h-.016zm0 9.6h-4.176v-6.816h4.24a3.433 3.433 0 010 6.864l-.064-.048zm18.448-2.88a5.598 5.598 0 00-5.328 3.056l2.656 1.104a2.83 2.83 0 012.72-1.472 2.877 2.877 0 013.2 2.576v.208a6.61 6.61 0 00-3.12-.768c-2.864 0-5.76 1.6-5.76 4.496a4.624 4.624 0 004.96 4.4 4.207 4.207 0 003.84-1.92h.096V48h2.88v-7.696c0-3.504-2.656-5.536-6.064-5.536l-.08-.048zm-.368 10.96c-.976 0-2.336-.496-2.336-1.696 0-1.6 1.696-2.128 3.2-2.128.95-.023 1.89.209 2.72.672a3.615 3.615 0 01-3.504 3.2l-.08-.048zm17.008-10.48l-3.424 8.672h-.096l-3.552-8.672h-3.2l5.328 12.128-3.04 6.736h3.12l8.192-18.864h-3.328zM171.088 48h2.992V28h-2.992v20zM75.772 16.484a4.336 4.336 0 01-1.2 3.2 4.657 4.657 0 01-3.52 1.424 5.04 5.04 0 01-3.536-8.592 4.799 4.799 0 013.536-1.44 4.96 4.96 0 011.968.4c.58.233 1.095.6 1.504 1.072l-.848.848a3.2 3.2 0 00-2.624-1.136 3.71 3.71 0 00-3.728 3.84 3.776 3.776 0 006.4 2.768 3.024 3.024 0 00.8-1.952h-3.472v-1.152h4.656c.043.238.064.479.064.72zm7.328-4h-4.32v3.04h3.936v1.152H78.78v3.04h4.32V20.9h-5.6v-9.6h5.6v1.184zm5.248 8.416h-1.232v-8.416h-2.688V11.3H91.1v1.184h-2.752V20.9zm7.456 0v-9.6h1.232v9.6h-1.232zm6.704 0h-1.232v-8.416h-2.688V11.3h6.592v1.184h-2.672V20.9zm15.168-1.248a4.992 4.992 0 01-7.04 0 5.185 5.185 0 010-7.12 4.962 4.962 0 017.04 0 5.167 5.167 0 010 7.12zm-6.128-.8a3.698 3.698 0 005.216 0 4.094 4.094 0 000-5.504 3.696 3.696 0 00-5.216 0 4.099 4.099 0 000 5.504zm9.28 2.048v-9.6h1.504l4.672 7.472V11.3h1.232v9.6h-1.28l-4.88-7.824V20.9h-1.248z" fill="#fff" data-v-4c1ace0e=""></path>
                                  <path fill-rule="evenodd" clip-rule="evenodd" d="M114.156 11.226a4.801 4.801 0 00-3.405 1.417 5.026 5.026 0 000 6.897 4.83 4.83 0 006.811 0 5.01 5.01 0 00-.001-6.896 4.79 4.79 0 00-3.405-1.418zm-1.968.074a5.124 5.124 0 015.602 1.12l.002.002a5.329 5.329 0 010 7.34l-.003.003a5.151 5.151 0 01-7.266 0l-.003-.003a5.346 5.346 0 010-7.34l.003-.003a5.115 5.115 0 011.665-1.12zm-41.14-.064a4.64 4.64 0 00-3.418 1.392 4.88 4.88 0 003.423 8.32h.005a4.496 4.496 0 003.399-1.375 4.175 4.175 0 001.155-3.082l.16-.007h-.16c0-.188-.013-.375-.04-.56h-4.36v.832h3.477l-.005.165a3.184 3.184 0 01-.842 2.056l-.007.006a3.936 3.936 0 01-6.67-2.881 3.872 3.872 0 013.883-4.002 3.36 3.36 0 012.635 1.063l.62-.62a3.792 3.792 0 00-1.343-.919h-.003a4.8 4.8 0 00-1.904-.388h-.004zm4.884 5.244a4.496 4.496 0 01-1.244 3.315 4.818 4.818 0 01-3.64 1.473 5.2 5.2 0 01-3.646-8.864 4.96 4.96 0 013.652-1.488 5.12 5.12 0 012.028.412 4.112 4.112 0 011.563 1.115l.098.113-1.077 1.077-.112-.134a3.04 3.04 0 00-2.493-1.08l-.01.001a3.551 3.551 0 00-3.567 3.675v.007a3.616 3.616 0 006.126 2.654 2.864 2.864 0 00.744-1.68h-3.462v-1.472h4.95l.024.132c.044.245.066.495.066.744zm1.408-5.34h5.92v1.504h-4.32v2.72h3.936v1.472H78.94v2.72h4.32v1.504h-5.92v-9.92zm.32.32v9.28h5.28v-.864h-4.32v-3.36h3.936v-.832H78.62v-3.36h4.32v-.864h-5.28zm6.608-.32h6.992v1.504h-2.752v8.416h-1.552v-8.416h-2.688V11.14zm.32.32v.864h2.688v8.416h.912v-8.416h2.752v-.864h-6.352zm11.056-.32h1.552v9.92h-1.552v-9.92zm.32.32v9.28h.912v-9.28h-.912zm2.464-.32h6.912v1.504h-2.672v8.416h-1.552v-8.416h-2.688V11.14zm.32.32v.864h2.688v8.416h.912v-8.416h2.672v-.864h-6.272zm21.92-.32h1.753l4.423 7.074V11.14h1.552v9.92h-1.529l-4.631-7.425v7.425h-1.568v-9.92zm.32.32v9.28h.928v-8.223l5.129 8.223h1.031v-9.28h-.912v7.87l-4.921-7.87h-1.255zm-9.553 1.775a3.857 3.857 0 015.442 0l.006.005a4.257 4.257 0 010 5.72l-.006.005a3.855 3.855 0 01-5.442 0l.113-.113-.118.107a4.253 4.253 0 010-5.718l.005-.006zm.229 5.506a3.535 3.535 0 004.984 0 3.932 3.932 0 000-5.282 3.534 3.534 0 00-4.984 0 3.937 3.937 0 000 5.282z" fill="#fff" data-v-4c1ace0e=""></path>
                                  <path d="M16.704 12.064a3.2 3.2 0 00-.736 2.24v35.392a3.2 3.2 0 00.736 2.24l.112.112L36.64 32.24v-.464L16.816 11.952l-.112.112z" fill="url(#paint0_linear)" data-v-4c1ace0e=""></path>
                                  <path d="M43.2 38.848l-6.56-6.608v-.464l6.56-6.624.144.08 7.856 4.464c2.24 1.264 2.24 3.344 0 4.624l-7.824 4.448-.176.08z" fill="url(#paint1_linear)" data-v-4c1ace0e=""></path>
                                  <path d="M43.392 38.752L36.64 32 16.704 51.936a2.607 2.607 0 003.328.096l23.376-13.28" fill="url(#paint2_linear)" data-v-4c1ace0e=""></path>
                                  <path d="M43.392 25.248l-23.376-13.28a2.608 2.608 0 00-3.328.096L36.64 32l6.752-6.752z" fill="url(#paint3_linear)" data-v-4c1ace0e=""></path>
                                  <path opacity=".2" d="M43.2 38.608l-23.184 13.2a2.673 2.673 0 01-3.2 0l-.112.112.112.112a2.656 2.656 0 003.2 0l23.376-13.28-.192-.144z" fill="#000" data-v-4c1ace0e=""></path>
                                  <path opacity=".12" d="M16.704 51.712a3.2 3.2 0 01-.736-2.24v.24a3.2 3.2 0 00.736 2.24l.112-.112-.112-.128zM51.2 34.08l-8 4.528.144.144 7.856-4.448A2.8 2.8 0 0052.8 32a2.976 2.976 0 01-1.6 2.08z" fill="#000" data-v-4c1ace0e=""></path>
                                  <path opacity=".25" d="M20.016 12.191L51.2 29.92A2.975 2.975 0 0152.8 32a2.8 2.8 0 00-1.6-2.304L20.016 11.967c-2.24-1.264-4.064-.208-4.064 2.352v.24c.048-2.576 1.84-3.632 4.064-2.368z" fill="#fff" data-v-4c1ace0e=""></path>
                                  <defs data-v-4c1ace0e="">
                                    <linearGradient id="paint0_linear" x1="34.88" y1="13.936" x2="8.032" y2="40.784" gradientUnits="userSpaceOnUse" data-v-4c1ace0e="">
                                      <stop stop-color="#00A0FF" data-v-4c1ace0e=""></stop>
                                      <stop offset=".01" stop-color="#00A1FF" data-v-4c1ace0e=""></stop>
                                      <stop offset=".26" stop-color="#00BEFF" data-v-4c1ace0e=""></stop>
                                      <stop offset=".51" stop-color="#00D2FF" data-v-4c1ace0e=""></stop>
                                      <stop offset=".76" stop-color="#00DFFF" data-v-4c1ace0e=""></stop>
                                      <stop offset="1" stop-color="#00E3FF" data-v-4c1ace0e=""></stop>
                                    </linearGradient>
                                    <linearGradient id="paint1_linear" x1="54.128" y1="32" x2="15.424" y2="32" gradientUnits="userSpaceOnUse" data-v-4c1ace0e="">
                                      <stop stop-color="#FFE000" data-v-4c1ace0e=""></stop>
                                      <stop offset=".41" stop-color="#FFBD00" data-v-4c1ace0e=""></stop>
                                      <stop offset=".78" stop-color="orange" data-v-4c1ace0e=""></stop>
                                      <stop offset="1" stop-color="#FF9C00" data-v-4c1ace0e=""></stop>
                                    </linearGradient>
                                    <linearGradient id="paint2_linear" x1="39.728" y1="35.68" x2="3.312" y2="72.08" gradientUnits="userSpaceOnUse" data-v-4c1ace0e="">
                                      <stop stop-color="#FF3A44" data-v-4c1ace0e=""></stop>
                                      <stop offset="1" stop-color="#C31162" data-v-4c1ace0e=""></stop>
                                    </linearGradient>
                                    <linearGradient id="paint3_linear" x1="11.68" y1=".288" x2="27.936" y2="16.544" gradientUnits="userSpaceOnUse" data-v-4c1ace0e="">
                                      <stop stop-color="#32A071" data-v-4c1ace0e=""></stop>
                                      <stop offset=".07" stop-color="#2DA771" data-v-4c1ace0e=""></stop>
                                      <stop offset=".48" stop-color="#15CF74" data-v-4c1ace0e=""></stop>
                                      <stop offset=".8" stop-color="#06E775" data-v-4c1ace0e=""></stop>
                                      <stop offset="1" stop-color="#00F076" data-v-4c1ace0e=""></stop>
                                    </linearGradient>
                                  </defs>
                                </svg>
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div data-v-4c1ace0e="" class="w-full md:w-2/6 footer_nav pt-5 px-5 md:px-0">
                    <div data-v-4c1ace0e="" class="md:ml-20">
                      <div data-v-4c1ace0e="" class="text-lg fb mt-5 uppercase text-white font-normal tracking-wider pb-3 footer-title">{{ __('SUPPORT CENTER')}}</div>

            @if (!empty($settings->whatsapp_number))
                      <div data-v-4c1ace0e="" class="ff-bf ffont-medium">
                        <a data-v-4c1ace0e="" href="https://wa.me/{{ $settings->whatsapp_number }}" target="_blank" class="rounded-md p-3 mt-2 md:mt-4 flex footer-contact-icon1">
                          <div data-v-4c1ace0e="" class="footer-contact-icon">
                            <svg data-v-4c1ace0e="" height="34" color="#dfdfdf" width="34" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="whatsapp" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-whatsapp fa-w-14 fa-2x mr-2 pl-2">
                              <path data-v-4c1ace0e="" fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" class=""></path>
                            </svg>
                          </div>
                          <div data-v-4c1ace0e="" class="ml-2 pl-2" style="border-left: 2px solid var(--footer-font-color);">
                            <p data-v-4c1ace0e="" class="text-primary text-opacity-70 text-xs font-normal text-white"> Help line [{{ $settings->support_time }}] </p>
                            <span data-v-4c1ace0e="" class="number">{{ $settings->whatsapp_number }}</span>
                          </div>
                        </a>
                      </div>
            @endif


                    </div>
                  </div>
                </div>
              </div>
            </section>
            <div style="border-top:2px solid #c1bcbc1c;" data-v-4c1ace0e="">
              <div class="pb-5 px-5 m-auto pt-5 text-white text-sm flex justify-center" data-v-4c1ace0e="">
                <div class="mt-2 text-center fb tracking-wide" data-v-4c1ace0e="" style="color:var(--footer-font-color);opacity:0.8;"> {{ __('© Copyright') }} {{ date('Y') }}
        {{ $settings->site_name }}{{ __('. All Rights Reserved.') }} Developed By <span style="color: #007bff;">Mac GunJon</span>
                </div>
              </div>
            </div>

              @if ($settings->footer_menu)
        <div class="sticky-footer-container">
            @foreach ($menus as $menu)
                @if ($menu->isBoth())
                    <div class="sticky-footer-item">
                        <a href="{{ $menu->link }}">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <span>{!! $menu->icon !!}</span>
                                <span>{{ $menu->name }}</span>
                            </div>
                        </a>
                    </div>
                @endif

                @if ($menu->isUser() && auth()->check())
                    <div class="sticky-footer-item">
                        <a href="{{ $menu->link }}">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <span>{!! $menu->icon !!}</span>
                                <span>{{ $menu->name }}</span>
                            </div>
                        </a>
                    </div>
                @endif

                @if ($menu->isGuest() && auth()->guest())
                    <div class="sticky-footer-item">
                        <a href="{{ $menu->link }}">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <span>{!! $menu->icon !!}</span>
                                <span>{{ $menu->name }}</span>
                            </div>
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
          </footer>
    {{-- Footer End --}}


    {{-- Jquery --}}
    <script src="{{ asset('assets/template/js/jquery-3.7.1.min.js') }}"></script>

    {{-- Bootstrap --}}
    <script src="{{ asset('assets/template/js/bootstrap/bootstrap.bundle.min.js') }}"></script>

    {{-- Toastr --}}
    <script src="{{ asset('assets/template/js/toastr/toastr.min.js') }}"></script>

    {{-- Custom JS --}}
    <script>
        $(document).ready(function() {
  $('.top-profile').click(function(event) {
    event.stopPropagation();
    $('#userMenu').toggleClass('hidden');
  });
  $('.nav-overlay').click(function(event) {
    event.stopPropagation();
    $('#userMenu').addClass('hidden');
  });

});
        $(document).ready(function() {
            $('#accountButton').click(function() {
                $('.right-side-menu').toggleClass('active');
                $('#overlay').toggle();
            });

            $('#closeButton').click(function() {
                $('.right-side-menu').removeClass('active');
                $('#overlay').hide();
            });

            $('#overlay').click(function() {
                $('.right-side-menu').removeClass('active');
                $('#overlay').hide();
            });
        });

        // PWA Install
        let deferredPrompt;
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.classList.remove('hidden');
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                installBtn.classList.add('hidden');
            }
        });

        window.addEventListener('appinstalled', () => {
            installBtn.classList.add('hidden');
        });
    </script>

    @include('scripts.alert')

    @stack('script')

    {{ $settings->footer_js }}
</div>
</body>

</html>
