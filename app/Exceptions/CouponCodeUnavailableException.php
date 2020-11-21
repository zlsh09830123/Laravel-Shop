<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Exception;

class CouponCodeUnavailableException extends Exception
{
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 當這個異常被觸發時，會呼叫 render() 方法來輸出給用戶
    public function render(Request $request)
    {
        // 如果用戶透過 API 請求，則返回 JSON 格式的錯誤訊息
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->message], $this->code);
        }
        // 否則返回上一頁並帶上錯誤訊息
        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}
