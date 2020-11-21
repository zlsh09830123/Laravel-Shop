<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show($code)
    {
        // 判斷優惠券是否存在
        if (!$record = CouponCode::where('code', $code)->first()) {
            abort(404);
        }

        // 如果優惠券沒有啟用，則等同優惠券不存在
        if (!$record->enabled) {
            abort(404);
        }

        if ($record->total - $record->used <= 0) {
            return response()->json(['msg' => '此優惠券數量已被使用完畢'], 403);
        }

        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
            return response()->json(['msg' => '此優惠券現在還不能使用'], 403);
        }

        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
            return response()->json(['msg' => '此優惠券已過期'], 403);
        }

        return $record;
    }
}
