<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\CouponCode';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode());

        // 預設按建立時間倒序排序
        $grid->model()->orderBy('created_at', 'desc');
        $grid->id('ID')->sortable();
        $grid->name('名稱');
        $grid->code('優惠碼');
        $grid->description('描述');
        $grid->column('usage', '用量')->display(function($value) {
            return "{$this->used} / {$this->total}";
        });
        $grid->enabled('是否啟用')->display(function($value) {
            return $value ? '是' : '否';
        });
        $grid->created_at('建立時間');
        $grid->actions(function($actions) {
            $actions->disableView();
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
        $form = new Form(new CouponCode());

        $form->display('id', 'ID');
        $form->text('name', '名稱')->rules('required');
        $form->text('code', '優惠碼')->rules(function($form) {
            // 如果 $form->model()->id 不為空，代表是編輯操作
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,'.$id.',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', '類型')->options(CouponCode::$typeMap)->rules('required')->default(CouponCode::TYPE_FIXED);
        $form->text('value', '折扣')->rules(function($form) {
            if (request()->input('type') === CouponCode::TYPE_PERCENT) {
                // 如果選擇了百分比折扣類型，折扣範圍只能是 1 ~ 99
                return 'required|numeric|between:1,99';
            } else {
                // 否則只要大於 1 即可
                return 'required|numeric|min:1';
            }
        });
        $form->text('total', '總量')->rules('required|numeric|min:0');
        $form->text('min_amount', '最低金額')->rules('required|numeric|min:0');
        $form->datetime('not_before', '開始時間');
        $form->datetime('not_after', '結束時間');
        $form->radio('enabled', '啟用')->options(['1' => '是', '0' => '否']);

        $form->saving(function(Form $form) {
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;
    }
}
