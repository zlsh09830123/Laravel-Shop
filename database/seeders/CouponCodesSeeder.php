<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CouponCode;

class CouponCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CouponCode::factory()->count(20)->create();
    }
}
