@extends('layouts.app')

@section('title')
    {{ __('Reset Password') }} {{ __('-') }} {{ $settings->site_title }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-sm-6 mx-auto m-tb-50">
            <div class="content-box shadow">
                <h2 class="content-title">{{ __('Reset Password') }}</h2>
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="form-group form-group-icon">
                        <label class="control-label" for="login"><i class="fas fa-envelope"></i></label>
                        <input type="text" class="form-control" id="login"
                            value="{{ old('email', $request->email) }}" name="email" required placeholder="Email"
                            autocomplete="off">
                        @error('email')
                            <span class="alert alert-white">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    <div class="form-group form-group-icon">
                        <label class="control-label" for="password"><i class="fas fa-lock"></i></label>
                        <input type="password" class="form-control" id="password" name="password" required
                            placeholder="Password" autocomplete="off">

                        @error('password')
                            <span class="alert alert-white">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    <div class="form-group form-group-icon">
                        <label class="control-label" for="password_confirmation"><i class="fas fa-lock"></i></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            required placeholder="Confirm password" autocomplete="off">
                        @error('password_confirmation')
                            <span class="alert alert-white">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    <div class="component_button_submit">
                        <div class="form-group">
                            <div class="">
                                <button type="submit" name="change" class="btn btn-light theme-btn-block"
                                    id="change">{{ __('Change Password') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="text-center" style="margin-bottom:10px;">{{ __('Remember your password?') }} <a
                            href="{{ route('login') }}">{{ __('Sigin
                                                                                                                in') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
