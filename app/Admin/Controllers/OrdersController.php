<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;

class OrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '訂單';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        // 只展示已支付的訂單，並且預設按支付時間倒序排序
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

        $grid->no('訂單流水號');
        // 展示關聯關係的欄位時，使用 column() 方法
        $grid->column('user.name', '買家');
        $grid->total_amount('總金額')->sortable();
        $grid->paid_at('付款時間')->sortable();
        $grid->ship_status('物流')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('退款狀態')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });
        // 禁用建立按鈕，後台不需要建立訂單
        $grid->disableCreateButton();
        $grid->actions(function($actions) {
            // 禁用刪除和編輯按鈕
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->tools(function($tools) {
            // 禁用批量刪除按鈕
            $tools->batch(function($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    public function show($id, Content $content)
    {
        return $content
            ->header('查看訂單')
            // body() 方法可以接受 Laravel 的視圖做為參數
            ->body(view('admin.orders.show', ['order' => Order::find($id)]));
    }

    public function ship(Order $order, Request $request)
    {
        // 判斷當前訂單是否已支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('此訂單未付款');
        }
        // 判斷當前訂單出貨狀態是否為未出貨
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('此訂單已出貨');
        }
        // Laravel 5.5 之後 validate() 方法可以返回校驗過的值
        $validatedData = $request->validate([
            'express_company' => 'required',
            'express_no' => 'required',
        ], [], [
            'express_company' => '物流公司',
            'express_no' => '物流單號',
        ]);
        // 將訂單出貨狀態改為已出貨，並存入物流資訊
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            // 我們在 Order 模型的 $casts 屬型裡指明了 ship_data 是一個陣列
            // 因此這裡可以直接把陣列傳過去
            'ship_data' => $validatedData,
        ]);

        // 返回上一頁
        return redirect()->back();
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('no', __('No'));
        $show->field('user_id', __('User id'));
        $show->field('address', __('Address'));
        $show->field('total_amount', __('Total amount'));
        $show->field('remark', __('Remark'));
        $show->field('paid_at', __('Paid at'));
        $show->field('payment_method', __('Payment method'));
        $show->field('payment_no', __('Payment no'));
        $show->field('refund_status', __('Refund status'));
        $show->field('refund_no', __('Refund no'));
        $show->field('closed', __('Closed'));
        $show->field('reviewed', __('Reviewed'));
        $show->field('ship_status', __('Ship status'));
        $show->field('ship_data', __('Ship data'));
        $show->field('extra', __('Extra'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->text('no', __('No'));
        $form->number('user_id', __('User id'));
        $form->textarea('address', __('Address'));
        $form->number('total_amount', __('Total amount'));
        $form->textarea('remark', __('Remark'));
        $form->datetime('paid_at', __('Paid at'))->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', __('Payment method'));
        $form->text('payment_no', __('Payment no'));
        $form->text('refund_status', __('Refund status'))->default('pending');
        $form->text('refund_no', __('Refund no'));
        $form->switch('closed', __('Closed'));
        $form->switch('reviewed', __('Reviewed'));
        $form->text('ship_status', __('Ship status'))->default('pending');
        $form->textarea('ship_data', __('Ship data'));
        $form->textarea('extra', __('Extra'));

        return $form;
    }
}
