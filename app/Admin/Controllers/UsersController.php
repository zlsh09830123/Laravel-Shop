<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用戶';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        // 創建一個名為 ID 的欄位，內容是用戶的 ID
        $grid->id('ID');

        // 創建一個名為 用戶名 的欄位，內容是用戶的 name，下面的 email() 和 created_at() 同理
        $grid->name('用戶名');
        
        $grid->email('信箱');

        $grid->email_verified_at('已驗證信箱')->display(function($value) {
            return $value ? '是' : '否';
        });

        $grid->created_at('註冊時間');

        // 不在頁面顯示`新建`按鈕，因為我們不需要在後台新建用戶
        $grid->disableCreateButton();
        // 同時在每一行也不顯示`編輯`按鈕
        $grid->disableActions();

        $grid->tools(function($tools) {
            // 禁用批量刪除按鈕
            $tools->batch(function($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }
}
