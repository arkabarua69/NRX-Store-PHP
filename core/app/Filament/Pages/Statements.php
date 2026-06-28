<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class Statements extends Page
{
    protected static ?string $navigationLabel = 'Statements';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.statements';

    public $availableBalance, $todaysDeposited, $totalDeposited;
    public $todaysOrders, $todaysCompletedOrders, $todaysCancelledOrders, $todaysPendingOrders, $todaysProcessingOrders;
    public $newUsersToday, $totalUsers;
    public $availableCodes, $soldCodes, $todaysSoldCodes;
    public $AutoavailableCodes, $AutosoldCodes, $AutotodaysSoldCodes;
    public $todaysCompletedBilling, $yesterdaysCompletedBilling, $thisWeekCompletedBilling, $lastWeekCompletedBilling, $thisMonthCompletedBilling, $lastMonthCompletedBilling, $thisYearCompletedBilling, $lastYearCompletedBilling;
    public $todaysCompletedProfit, $yesterdaysCompletedProfit, $thisWeekCompletedProfit, $lastWeekCompletedProfit, $thisMonthCompletedProfit, $lastMonthCompletedProfit, $thisYearCompletedProfit, $lastYearCompletedProfit;
    public $todaysVisitors, $yesterdaysVisitors, $thisWeekVisitors, $thisMonthVisitors, $totalVisitors;

    public function mount()
    {
        $this->availableBalance = DB::table('users')->sum('balance');
        $this->todaysDeposited = DB::table('deposits')
            ->where('status', 'paid')
            ->whereDate('updated_at', today())
            ->sum('amount');
        $this->totalDeposited = DB::table('deposits')
            ->where('status', 'paid')
            ->sum('amount');

        $this->todaysOrders = DB::table('orders')
        ->whereDate('created_at', today())
        ->count();

        $this->todaysCompletedOrders = DB::table('orders')
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->count();

        $this->todaysCancelledOrders = DB::table('orders')
            ->where('status', 'cancelled')
            ->whereDate('updated_at', today())
            ->count();

        $this->todaysPendingOrders = DB::table('orders')
            ->where('status', 'pending')
            ->whereDate('updated_at', today())
            ->count();
        $this->todaysProcessingOrders = DB::table('orders')
            ->where('status', 'processing')
            ->whereDate('updated_at', today())
            ->count();

        $this->newUsersToday = DB::table('users')
        ->whereDate('created_at', today())
        ->count();

    $this->totalUsers = DB::table('users')
        ->count();

         $this->availableCodes = DB::table('vouchers')
        ->where('status', 1)
        ->count();

    $this->soldCodes = DB::table('vouchers')
        ->where('status', 0)
        ->whereNotNull('order_id')
        ->count();

    $this->todaysSoldCodes = DB::table('vouchers')
        ->where('status', 0)
        ->whereNotNull('order_id')
        ->whereDate('updated_at', today())
        ->count();

         $this->AutoavailableCodes = DB::table('auto_vouchers')
        ->where('status', 1)
        ->count();

    $this->AutosoldCodes = DB::table('auto_vouchers')
        ->where('status', 0)
        ->whereNotNull('order_id')
        ->count();

    $this->AutotodaysSoldCodes = DB::table('auto_vouchers')
        ->where('status', 0)
        ->whereNotNull('order_id')
        ->whereDate('updated_at', today())
        ->count();



    $this->todaysCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereDate('updated_at', today())
        ->sum('amount');

    $this->yesterdaysCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereDate('updated_at', today()->subDay())
        ->sum('amount');

    $this->thisWeekCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->sum('amount');

    $this->lastWeekCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereBetween('updated_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
        ->sum('amount');

    $this->thisMonthCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereMonth('updated_at', now()->month)
        ->whereYear('updated_at', now()->year)
        ->sum('amount');

    $this->lastMonthCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereMonth('updated_at', now()->subMonth()->month)
        ->whereYear('updated_at', now()->subMonth()->year)
        ->sum('amount');

    $this->thisYearCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereYear('updated_at', now()->year)
        ->sum('amount');

    $this->lastYearCompletedBilling = DB::table('orders')
        ->where('status', 'completed')
        ->whereYear('updated_at', now()->subYear()->year)
        ->sum('amount');

$this->todaysCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereDate('updated_at', today())
    ->sum('profit');

$this->yesterdaysCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereDate('updated_at', today()->subDay())
    ->sum('profit');

$this->thisWeekCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
    ->sum('profit');

$this->lastWeekCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereBetween('updated_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
    ->sum('profit');

$this->thisMonthCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereMonth('updated_at', now()->month)
    ->whereYear('updated_at', now()->year)
    ->sum('profit');

$this->lastMonthCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereMonth('updated_at', now()->subMonth()->month)
    ->whereYear('updated_at', now()->subMonth()->year)
    ->sum('profit');

$this->thisYearCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereYear('updated_at', now()->year)
    ->sum('profit');

$this->lastYearCompletedProfit = DB::table('orders')
    ->where('status', 'completed')
    ->whereYear('updated_at', now()->subYear()->year)
    ->sum('profit');



        $this->todaysVisitors = DB::table('visitors')
            ->whereDate('visited_at', today())
            ->count();

        $this->yesterdaysVisitors = DB::table('visitors')
            ->whereDate('visited_at', Carbon::yesterday())
            ->count();

        $this->thisWeekVisitors = DB::table('visitors')
            ->whereBetween('visited_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        $this->thisMonthVisitors = DB::table('visitors')
            ->whereBetween('visited_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        $this->totalVisitors = DB::table('visitors')->count();
    }
}
