@extends('layouts.app') 
@section('title') {{ __('My Account') }} {{ __('-') }} {{ $settings->site_title }} 
@endsection 
@section('content')
<!--- acoount page--->
<div>
  <div class="p-2">
    <div class="text-center">
      <button>
        <img src="https://ui-avatars.com/api/?name={{ strtoupper(substr($nameFirstLater, 0, 1)) }}&size=96&background={{ str_replace("#","",$settings->theme_color) }}&color=ffffff" class="user_style_profile">
      </button>
    </div>
    <div class="text-center w-full">
      <span class="capitalize mt-1 theme-color-text font-16">Hi, {{ $name }}</span>
    </div>
    <div class="text-center w-full flex mb-3 md:mb-8 justify-center items-center">
      <span class="capitalize primary-font-color-text font-16">
        <b class="font-bold">Available Balance :  {{ price($balance) }}</b>
      </span>
      <div class="border ml-2 p-1 rounded cursor-pointer">
        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path fill="currentColor" d="M2 12C2 16.97 6.03 21 11 21C13.39 21 15.68 20.06 17.4 18.4L15.9 16.9C14.63 18.25 12.86 19 11 19C4.76 19 1.64 11.46 6.05 7.05C10.46 2.64 18 5.77 18 12H15L19 16H19.1L23 12H20C20 7.03 15.97 3 11 3C6.03 3 2 7.03 2 12Z"></path>
        </svg>
      </div>
    </div>
    <div style="max-width: 700px; margin: auto;">
      <div class="text-center grid md:grid-cols-4 grid-cols-2 md:gap-4 gap-3 my-2 md:my-5 mb-10 statics-container">
        <div class="bg-white statics">
          <h2 class="text-lg font-normal fb-normal statics-heading">{{ price($balance) }}</h2>
          <h2 class="text-lg primary-font-color-text font-normal fb-normal">{{ __('Balance') }}</h2>
        </div>
        <div class="bg-white statics">
          <h2 class="text-lg font-normal fb-normall statics-heading">{{ $totalOrder }}</h2>
          <h2 class="text-lg primary-font-color-text font-normal fb-normal">{{ __('Total Order') }}</h2>
        </div>
        <div class="bg-white statics">
          <h2 class="text-lg font-normal fb-normall statics-heading">{{ price($totalSpent) }}</h2>
          <h2 class="text-lg primary-font-color-text font-normal fb-normal">{{ __('Total Spent') }}</h2>
        </div>
        <div class="bg-white statics">
          <h2 class="text-lg font-normal fb-normall statics-heading">{{ user_id() }}</h2>
          <h2 class="text-lg primary-font-color-text font-normal fb-normal">{{ __('Support PIN') }}</h2>
        </div>
      </div>
      <div class="w-full text-left bg-white my-4 account-info-container">
        <div class="text-left px-3 flex items-center">
          <svg class="mr-2" fill="#000000" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12,1A11,11,0,1,0,23,12,11.013,11.013,0,0,0,12,1Zm0,20a9.641,9.641,0,0,1-5.209-1.674,7,7,0,0,1,10.418,0A9.167,9.167,0,0,1,12,21Zm6.694-3.006a8.98,8.98,0,0,0-13.388,0,9,9,0,1,1,13.388,0ZM12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z" />
          </svg>
          <h2 class="text-lg primary-font-color-text py-2 font-normal fb"> {{ __('Personal Information') }}</h2>
        </div>
        <hr>
        <div class="px-4 py-2"> @error('phone') <div class="alert alert-white" role="alert">{{ $message }}</div> @enderror <form method="POST" action="{{ route('user.account.update') }}"> @csrf <label for="email" class="control-label">{{ __('Email') }}</label>
            <input type="text" class="form-control shadow" name="email" value="{{ old('email', $user->email) }}" required> @error('email') <div class="alert alert-white" role="alert">{{ $message }}</div> @enderror <label for="phone" class="control-label">{{ __('Phone Number') }}</label>
            <input type="text" class="form-control shadow" name="phone" value="{{ old('phone', $user->phone) }}" required> @error('phone') <div class="alert alert-white" role="alert">{{ $message }}</div> @enderror <div class="clearfix" align="center">
              <input type="submit" class="btn theme-btn-block shadow" value="Update">
            </div>
          </form>
        </div>
      </div>
      <div class="w-full text-left bg-white my-4 account-info-container">
        <div class="text-left px-3 flex items-center">
          <svg class="mr-2" width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 11V10C9 8.34 9.5 7 12 7C14.5 7 15 8.34 15 10V11" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 14.6C12.3314 14.6 12.6 14.3314 12.6 14C12.6 13.6687 12.3314 13.4 12 13.4C11.6686 13.4 11.4 13.6687 11.4 14C11.4 14.3314 11.6686 14.6 12 14.6Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M14.5 17H9.5C7.5 17 7 16.5 7 14.5V13.5C7 11.5 7.5 11 9.5 11H14.5C16.5 11 17 11.5 17 13.5V14.5C17 16.5 16.5 17 14.5 17Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <h2 class="text-lg primary-font-color-text py-2 font-normal fb"> {{ __('Change Password') }}</h2>
        </div>
        <hr>
        <div class="px-4 py-2">
          <form method="POST" action="{{ route('password.update') }}"> @csrf @method('PUT') <label class="control-label">{{ __('Current password') }}</label>
            <input type="password" class="form-control shadow" name="current_password" required> @error('current_password') <div class="alert alert-white" role="alert">{{ $message }}</div> @enderror <label class="control-label">{{ __('New password') }}</label>
            <input type="password" class="form-control shadow" name="password" required> @error('password') <div class="alert alert-white" role="alert">{{ $message }}</div> @enderror <label class="control-label">{{ __('Confirm new password') }}</label>
            <input type="password" class="form-control shadow" name="password_confirmation" required>
            <div class="clearfix" align="center">
              <input type="submit" name="updatePassword" class="btn theme-btn-block shadow" value="Submit">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--- /acoount page--->
@endsection