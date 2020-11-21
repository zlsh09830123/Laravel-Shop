<?php

namespace Database\Factories;

use App\Models\CouponCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CouponCode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 首先隨機取得一個類型
        $type = $this->faker->randomElement(array_keys(CouponCode::$typeMap));
        // 根據取得的類型生成對應折扣
        $value = $type === CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(1, 50);

        // 如果是固定金額，則最低訂單金額必須要比優惠金額高 1 元
        if ($type === CouponCode::TYPE_FIXED) {
            $minAmount = $value + 1;
        } else {
            // 如果是百分比折扣，有 50% 機率不需要最低訂單金額
            if (random_int(0, 100) < 50) {
                $minAmount = 0;
            } else {
                $minAmount = random_int(100, 1000);
            }
        }
        return [
            'name' => join(' ', $this->faker->words), // 隨機生成名稱
            'code' => CouponCode::findAvailableCode(), // 調用優惠碼生成方法
            'type' => $type,
            'value' => $value,
            'total' => 1000,
            'used' => 0,
            'min_amount' => $minAmount,
            'not_before' => null,
            'not_after' => null,
            'enabled' => true,
        ];
    }
}
