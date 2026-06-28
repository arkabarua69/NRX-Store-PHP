<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['user'])
            ->where('user_id', user_id())
            ->latest()
            ->paginate(gs()->paginate_per_page);

        return view('user.transactions', compact('transactions'));
    }
}
