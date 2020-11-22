<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Encore\Admin\Tree;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 從資料庫隨機取一個商品
        $product = Product::query()->where('onsale', true)->inRandomOrder()->first();
        // 從此商品的 SKU 中隨機取一個
        $sku = $product->skus()->inRandomOrder()->first();

        return [
            'amount' => random_int(1, 5), // 購買數量隨機 1 ~ 5 份
            'price' => $sku->price,
            'rating' => null,
            'review' => null,
            'reviewed_at' => null,
            'product_id' => $product->id,
            'product_sku_id' => $sku->id,
        ];
    }
}
