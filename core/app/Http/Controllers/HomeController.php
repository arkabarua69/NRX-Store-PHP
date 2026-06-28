<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class HomeController extends Controller
{
    public function home()
    {
        $sliders = Slider::with('media')
            ->where(['status' => Status::ACTIVE])
            ->orderBy('order_column')
            ->get();
        $products = Product::with('media')
            ->where(['status' => Status::ACTIVE])
            ->orderBy('order_column')
            ->get();
        return view('home', compact('sliders', 'products'));
    }

    public function page(string $slug)
    {
        $page = Page::where(['slug' => $slug, 'status' => Status::ACTIVE])->first();
        if (!$page) {
            abort(404);
        }
        return view('page', compact('page'));
    }

    public function topup(string $slug)
    {
        $product = Product::with(['variations.vouchers' => function ($query) {
            $query->where('status', Status::AVAILABLE);
        }])->where('status', Status::ACTIVE)->where(['slug' => $slug])->first();

        if (!$product) {
            abort(404);
        }

        return view('topup.checkout', compact('product'));
    }

    public function getPopups(Collection $collection, Request $request)
    {
        $popupQuery = Popup::query();
        if (!$request->session()->has('first_visit_popup')) {
            $firstVisitPopups = $popupQuery->where('type', Status::ONCE)
                ->where('status', Status::ACTIVE)
                ->get();
            $collection = $collection->merge($firstVisitPopups);
            $request->session()->put('first_visit_popup', true);
        }

        if (!$request->cookie('daily_popup_showed')) {
            $dailyOncePopups = $popupQuery->where('type', Status::DAILY)
                ->where('status', Status::ACTIVE)
                ->get();
            $collection = $collection->merge($dailyOncePopups);
            return response()->json(['popups' => $collection])->withCookie(cookie('daily_popup_showed', true, 1440));
        }

        return response()->json(['popups' => $collection]);
    }

    public function uidcheck(Request $request)
    {
        $id = $request->input('id');
        $nickname = $this->getPlayerNicknameFromUid($id);

        if ($nickname) {
            return response()->json(['nickname' => $nickname]);
        }

        return response()->json([]);
    }

    private function getPlayerNicknameFromUid(string $uid): ?string
    {
        $tableName = 'players';

        try {
            $nickname = DB::table($tableName)
                ->where('uid', $uid)
                ->value('nickname');

            return $nickname ?: null;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }
}
