<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class CartService
{
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();
        // 從資料庫中查詢該商品是否已經在購物車中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在則直接疊加商品數量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            // 否則建立一個新的購物車記錄
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    public function remove($skuIds)
    {
        // 可以傳單個 ID，也可以傳 ID 陣列
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}