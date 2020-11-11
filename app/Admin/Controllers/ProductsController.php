<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->id('ID')->sortable();
        $grid->title('商品名稱');
        $grid->on_sale('已上架')->display(function($value) {
            return $value ? '是' : '否';
        });
        $grid->price('價格');
        $grid->rating('評分');
        $grid->sold_count('銷量');
        $grid->review_count('評論數');
        $grid->actions(function($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function($tools) {
            // 禁用批量刪除按鈕
            $tools->batch(function($batch) {
                $batch->disableDelete();
            });
        });
        
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        //  創建一個輸入框，第一個參數 title 是模型的欄位名，第二個參數是欄位描述
        $form->text('title', '商品名稱')->rules('required');

        // 創建一個選擇圖片的眶
        $form->image('image', '封面圖片')->rules('required|image');

        // 創建一個文字編輯器
        $form->quill('description', '商品描述')->rules('required');

        // 創建一組單選框
        $form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');

        // 直接添加一對多的關聯模型
        $form->hasMany('skus', 'SKU 列表', function(Form\NestedForm $form) {
            $form->text('title', 'SKU 名稱')->rules('required');
            $form->text('description', 'SKU 描述')->rules('required');
            $form->text('price', '單價')->rules('required|numeric|min:1');
            $form->text('stock', '剩餘庫存')->rules('required|integer|min:0');
        });

        // 定義事件回調，當模型即將保存時會觸發這個回調
        $form->saving(function(Form $form) {
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
        });

        return $form;
    }
}
