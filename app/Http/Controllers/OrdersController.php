<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewed;
use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use App\Http\Requests\SendReviewRequest;
use App\Http\Requests\ApplyRefundRequest;

class OrdersController extends Controller
{
    // 利用 Laravel 的自動解析功能注入 CartService 類別
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with() 方法預加載，避免 N+1 問題
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();
        
        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function received(Order $order, Request $request)
    {
        // 驗證權限
        $this->authorize('own', $order);

        // 判斷訂單的出貨狀態是否為已出貨
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('出貨狀態不正確');
        }

        //更新出貨狀態為已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        // 返回原頁面
        return $order;
    }

    public function review(Order $order)
    {
        // 驗證權限
        $this->authorize('own', $order);
        // 判斷是否已經支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('此訂單未支付，無法評價');
        }
        // 使用 load() 方法加載關聯資料，避免 N+1 問題
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 驗證權限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('此訂單未支付，無法評價');
        }
        // 判斷是否已經評價
        if ($order->reviewed) {
            throw new InvalidRequestException('此訂單已評價，不可重複提交');
        }
        $reviews = $request->input('reviews');
        // 開啟事務
        \DB::transaction(function() use ($reviews, $order) {
            // 遍歷用戶提交的資料
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存評分和評價
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            // 將訂單標記為已評價
            $order->update(['reviewed' => true]);
        });
        event(new OrderReviewed($order));

        return redirect()->back();
    }

    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        // 驗證訂單是否屬於當前用戶
        $this->authorize('own', $order);
        // 判斷訂單是否已付款
        if (!$order->paid_at) {
            throw new InvalidRequestException('此訂單未付款，不可退款');
        }
        // 判斷訂單退款狀態是否正確
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('此訂單已經申請過退款，請勿重複申請');
        }
        // 將用戶輸入的退款理由放到訂單的 extra 欄位中
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 將訂單退款狀態改為已申請退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra,
        ]);

        return $order;
    }
}
