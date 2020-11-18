<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// implements ShouldQueue 代表此監聽器是異步執行的
class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    // Laravel 會預設執行監聽器的 handle() 方法，觸發的事件會作為 handle() 方法的參數
    public function handle(OrderPaid $event)
    {
        // 從事件對象中取出對應的訂單
        $order = $event->getOrder();
        // 預加載商品資料
        $order->load('items.product');
        // 循環遍歷訂單的商品
        foreach ($order->items as $item) {
            $product = $item->product;
            // 計算對應商品的銷量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function($query) {
                    $query->whereNotNull('paid_at'); // 關聯的訂單狀態是已支付
                })->sum('amount');
            // 更新商品銷量
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }
    }
}
