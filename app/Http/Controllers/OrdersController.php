<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user = $request->user();
        // 開啟一個資料庫事務
        $order = DB::transaction(function() use ($user, $request) {
            $address = UserAddress::find($request->input('address_id'));
            // 更新此地址的最後使用時間
            $address->update(['last_used_at' => Carbon::now()]);
            // 建立一份訂單
            $order = new Order([
                'address' => [ // 將收件地址資料放進訂單中
                    'address' => $address->full_address,
                    'zip_code' => $address->zip_code,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            // 訂單關聯到當前用戶
            $order->user()->associate($user);
            // 寫入資料庫
            $order->save();

            $totalAmount = 0;
            $items = $request->input('items');
            // 遍歷用戶提交的 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 建立一個 OrderItem 並直接與當前訂單關聯
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('此商品庫存不足');
                }
            }

            // 更新訂單總金額
            $order->update(['total_amount' => $totalAmount]);

            // 將下單的商品從購物車中移除
            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();

            return $order;
        });

        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
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
}
