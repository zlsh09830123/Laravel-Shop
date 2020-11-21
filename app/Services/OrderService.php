<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;
use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;

class OrderService
{
    // 加入 $coupon 參數，可以為 null
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $coupon = null)
    {
        // 如果傳入優惠券，則先檢查是否可用
        if ($coupon) {
            // 但此時我們還沒有計算出訂單總金額，因此先不驗證
            $coupon->checkAvailable($user);
        }

        // 開啟一個資料庫事務，注意這裡也把 $coupon 放入 use() 中
        $order = DB::transaction(function() use ($user, $address, $remark, $items, $coupon) {
            // 更新此地址的最後使用時間
            $address->update(['last_used_at' => Carbon::now()]);
            // 建立一份訂單
            $order = new Order([
                'address' => [ // 將收件地址資料放進訂單中
                    'address' => $address->full_address,
                    'zip_code' => $address->zip_code,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            // 訂單關聯到當前用戶
            $order->user()->associate($user);
            // 寫入資料庫
            $order->save();

            $totalAmount = 0;
            // 遍歷用戶提交的 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 建立一個 OrderItem 並直接與當前訂單關聯
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('此商品庫存不足');
                }
            }
            if ($coupon) {
                // 訂單總金額已經計算出來，檢查是否符合優惠券規則
                $coupon->checkAvailable($user, $totalAmount);
                // 把訂單金額修改為優惠後的金額
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 將訂單與優惠券關聯
                $order->couponCode()->associate($coupon);
                // 增加優惠券的用量，需判斷返回值
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('此優惠券數量已被使用完畢');
                }
            }

            // 更新訂單總金額
            $order->update(['total_amount' => $totalAmount]);

            // 將下單的商品從購物車中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 直接使用 dispatch 函數
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}