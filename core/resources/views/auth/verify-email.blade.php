@extends('layouts.app')

@section('title'){{ __('Verify Email') }} {{ __('-') }} {{ $settings->site_title }}@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-6 mx-auto m-tb-50">
            <div class="content-box shadow">
                <h2 class="content-title">{{ __('Verify Email') }}</h2>
                <div class="alert alert-success" role="alert">
                    {{ __('Verify your email address by clicking on the link we just emailed to you. If you didn\'t receive the email, we will gladly send you another.') }}
                </div>
                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-green" role="alert">
                        {{ __('A new verification link has been sent to the email address you provided.') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <div class="component_button_submit">
                        <div class="form-group">
                            <div class="">
                                <button type="submit" name="reset" class="btn btn-light theme-btn-block">
                                    {{ __('Resend Verification Email') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="text-center" style="margin-bottom:10px;"><a
                            href="{{ route('logout') }}">{{ __('Sign Out') }}</a></div>
                </form>
            </div>
        </div>
    </div>
@endsection
