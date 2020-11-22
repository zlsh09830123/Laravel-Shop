<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 建立 100 筆訂單
        $orders = Order::factory()->count(100)->create();
        // 被購買的商品，用於後面更新商品銷量和評分
        $products = collect([]);
        foreach ($orders as $order) {
            // 每筆訂單隨機買 1 ~ 3 個商品
            $items = OrderItem::factory()->count(random_int(1, 3))->create([
                'order_id' => $order->id,
                'rating' => $order->reviewed ? random_int(1, 5) : null, // 隨機評分 1 ~ 5
                'review' => $order->reviewed ? $this->faker->sentence : null,
                'reviewed_at' => $order->reviewed ? $this->faker->dateTimeBetween($order->paid_at) : null, // 評價時間不能早於支付時間
            ]);

            // 計算總價
            $total = $items->smu(function(OrderItem $item) {
                return $item->price * $item->amount;
            });

            // 如果有優惠券，則計算優惠後價格
            if ($order->couponCode) {
                $total = $order->couponCode->getAdjustedPrice($total);
            }
            
            // 更新訂單總價
            $order->update([
                'total_amount' => $total,
            ]);

            // 將這筆訂單的商品合併到商品集合中
            $products = $products->merge($items->pluck('product'));
        }

        // 根據商品 ID 過濾掉重複的商品
        $products->unique('id')->each(function(Product $product) {
            // 查出此商品的銷量、評分、評價數
            $result = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    DB::raw('count(*) as review_count'),
                    DB::raw('avg(rating) as rating'),
                    DB::raw('sum(amount) as sold_count'),
                ]);
                    
            $product->update([
                'rating' => $result->rating ?: 5, // 如果某個商品沒有評分，則預設為 5 分
                'review_count' => $result->review_count,
                'sold_count' => $result->sold_count,
            ]);
        });
    }
}
