<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\CodeController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\PWAController;

Route::get('/manifest.json', [PWAController::class, 'manifestJson'])->name('manifest');
Route::get('/offline.html', [PWAController::class, 'offline']);


Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/topup/{slug}', [HomeController::class, 'topup'])->name('topup');
Route::get('/page/{slug}', [HomeController::class, 'page'])->name('page');
Route::get('/get-popup', [HomeController::class, 'getPopups'])->name('popup');

Route::group(['middleware' => ['auth', 'throttle:60,1'], 'as' => 'user.'], function () {
    // Deposit
    Route::get('/add-funds', [DepositController::class, 'index'])->name('addfunds');
    Route::post('/deposit/addfund', [DepositController::class, 'addFund'])->name('deposit.addfund')->middleware('throttle:5,1');
    Route::get('/deposit/pay', [DepositController::class, 'payNow'])->name('deposit.pay');

    // Order
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/codes', [CodeController::class, 'index'])->name('codes');
    Route::post('/topup/buynow', [OrderController::class, 'addOrder'])->name('topup.buynow')->middleware('throttle:10,1');
    Route::get('/order/pay', [OrderController::class, 'payNow'])->name('order.pay');


    // User
    Route::get('/account', [UserController::class, 'account'])->name('account');
    Route::post('/account/update', [UserController::class, 'update'])->name('account.update');

    // Transaction
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');

    // Payment Gateway
    Route::match(['get', 'post'], '/deposit/{trx}/{gateway}', [PaymentController::class, 'depositIpn'])->name('deposit.ipn');
    Route::match(['get', 'post'], '/order/{trx}/{gateway}', [PaymentController::class, 'orderIpn'])->name('order.ipn');
    Route::match(['get', 'post'], '/deposit/cancel', [PaymentController::class, 'depositCancel'])->name('deposit.cancel');
    Route::match(['get', 'post'], '/order/cancel', [PaymentController::class, 'orderCancel'])->name('order.cancel');
    Route::match(['get', 'post'], '/code/cancel', [PaymentController::class, 'codeCancel'])->name('code.cancel');
});

Route::post('/addons/uidcheck', [HomeController::class, 'uidcheck'])->middleware('throttle:10,1')->name('LaravelAddons::uidcheck');

require __DIR__ . '/auth.php';
