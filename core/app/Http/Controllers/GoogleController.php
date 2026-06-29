<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function signInwithGoogle()
    {
        $redirectUrl = url('callback/google');
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }
    public function callbackToGoogle(Request $request)
    {
        try {
            $user = Socialite::driver('google')->user();

            $findUser = User::where('email', $user->email)->first();

            if ($findUser) {
                Auth::login($findUser, true);
                return redirect(route('home'));
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'gauth_id' => $user->id,
                    'password' => Hash::make(strRandom())
                ]);

                if ($newUser->markEmailAsVerified()) {
                    event(new Verified($newUser));
                }

                Auth::login($newUser, true);
                return redirect(route('home'));
            }
        } catch (Exception $e) {
            return redirect(route('login'))->with('error', __('Google login failed. Please try again.'));
        }
    }
}
