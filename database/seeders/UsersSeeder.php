<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 透過 factory 方法生成 100 個用戶並儲存在資料庫
        User::factory()->count(100)->create();
    }
}
