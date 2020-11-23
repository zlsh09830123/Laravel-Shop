<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CouponCode;
use App\Models\OrderItem;
use App\Models\User;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 隨機取一個用戶
        $user = User::query()->inRandomOrder()->first();
        // 隨機取一個此用戶的地址
        $address = $user->addresses()->inRandomOrder()->first();
        // 10% 機率把訂單標記為退款
        $refund = random_int(0, 10) < 1;
        // 隨機生成出貨狀態
        $ship = $this->faker->randomElement(array_keys(Order::$shipStatusMap));
        // 優惠券
        $coupon = null;
        // 30% 機率此訂單使用優惠券
        if (random_int(0, 10) < 3) {
            // 為了避免出現邏輯錯誤，我們只選擇沒有最低金額限制的優惠券
            $coupon = CouponCode::query()->where('min_amount', 0)->inRandomOrder()->first();
            // 增加優惠券的使用量
            $coupon->changeUsed();
        }

        return [
            'address' => [
                'address' => $address->full_address,
                'zip_code' => $address->zip_code,
                'contact_name' => $address->contact_name,
                'contact_phone' => $address->contact_phone,
            ],
            'total_amount' => 0,
            'remark' => $this->faker->sentence,
            'paid_at' => $this->faker->dateTimeBetween('-30 days'), // 30 天前到現在任意時間點
            'payment_method' => 'ecpay',
            'payment_no' => $this->faker->uuid,
            'refund_status' => $refund ? Order::REFUND_STATUS_SUCCESS : Order::REFUND_STATUS_PENDING,
            'refund_no' => $refund ? Order::getAvailableRefundNo() : null,
            'closed' => false,
            'reviewed' => random_int(0, 10) > 2,
            'ship_status' => $ship,
            'ship_data' => $ship === Order::SHIP_STATUS_PENDING ? null : [
                'express_company' => $this->faker->company,
                'express_no' => $this->faker->uuid,
            ],
            'extra' => $refund ? ['refund_reason' => $this->faker->sentence] : [],
            'user_id' => $user->id,
            'coupon_code_id' => $coupon ? $coupon->id : null,
        ];
    }
}
