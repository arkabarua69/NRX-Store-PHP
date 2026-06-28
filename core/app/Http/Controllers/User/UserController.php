<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Order;
use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccountUpdateRequest;

class UserController extends Controller
{
    public function account()
    {
        $user = User::select(['name', 'email', 'phone', 'balance'])->find(user_id());
        $orderQuery = Order::where('user_id', user_id());

        $data = [
            'nameFirstLater' => strtoupper(substr($user->name, 0, 1)),
            'name'  => $user->name,
            'balance'   => $user->balance,
            'totalOrder'    => $orderQuery->count(),
            'totalSpent'    => $orderQuery->whereIn('status', [Status::COMPLETED, Status::PROCESSING])->sum('amount'),
            'user'  => $user
        ];
        return view('user.account', $data);
    }

    public function update(AccountUpdateRequest $request)
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('user.account')->with('success', __('Profile has been successfully updated.'));
    }
}
