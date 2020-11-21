@extends('layouts.app')

@section('title', '查看訂單')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-header">
    <h4>訂單詳情</h4>
  </div>
  <div class="card-body">
    <table class="table">
      <thead>
      <tr>
        <th>商品資訊</th>
        <th class="text-center">單價</th>
        <th class="text-center">數量</th>
        <th class="text-right item-amount">總計</th>
      </tr>
      </thead>
      @foreach($order->items as $index => $item)
        <tr>
          <td class="product-info">
            <div class="preview">
              <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                <img src="{{ $item->product->image_url }}">
              </a>
            </div>
            <div>
              <span class="product-title">
                 <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
              </span>
              <span class="sku-title">{{ $item->productSku->title }}</span>
            </div>
          </td>
          <td class="sku-price text-center vertical-middle">${{ number_format($item->price) }}</td>
          <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
          <td class="item-amount text-right vertical-middle">${{ number_format($item->price * $item->amount) }}</td>
        </tr>
      @endforeach
      <tr><td colspan="4"></td></tr>
    </table>
    <div class="order-bottom">
      <div class="order-info">
        <div class="line"><div class="line-label">收件地址：</div><div class="line-value">{{ join(' ', $order->address) }}</div></div>
        <div class="line"><div class="line-label">訂單備註：</div><div class="line-value">{{ $order->remark ?: '-' }}</div></div>
        <div class="line"><div class="line-label">訂單編號：</div><div class="line-value">{{ $order->no }}</div></div>
        <!-- 輸出物流狀態 -->
        <div class="line">
          <div class="line-label">物流狀態：</div>
          <div class="line-value">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</div>
        </div>
        <!-- 如果有物流資訊則顯示 -->
        @if($order->ship_data)
        <div class="line">
          <div class="line-label">物流資訊：</div>
          <div class="line-value">{{ $order->ship_data['express_company'] }} {{ $order->ship_data['express_no'] }}</div>
        </div>
        @endif
        <!-- 訂單已支付，且退款狀態不是未退款時顯示退款資訊 -->
        @if($order->paid_at && $order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
        <div class="line">
          <div class="line-label">退款狀態：</div>
          <div class="line-value">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}</div>
        </div>
        <div class="line">
          <div class="line-label">退款理由：</div>
          <div class="line-value">{{ $order->extra['refund_reason'] }}</div>
        </div>
        @endif
      </div>
      <div class="order-summary text-right">
        <!-- 顯示優惠券資訊開始 -->
        @if ($order->couponCode)
        <div class="text-primary">
          <span>優惠券：</span>
          <div class="value">{{ $order->couponCode->description }}</div>
        </div>
        @endif
        <!-- 顯示優惠券資訊結束 -->
        <div class="total-amount">
          <span>訂單總價：</span>
          <div class="value">${{ number_format($order->total_amount) }}</div>
        </div>
        <div>
          <span>訂單狀態：</span>
          <div class="value">
            @if($order->paid_at)
              @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                已支付
              @else
                {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
              @endif
            @elseif($order->closed)
              已關閉
            @else
              未支付
            @endif
          </div>
        </div>
        @if(isset($order->extra['refund_disagree_reason']))
        <div>
          <span>拒絕退款理由：</span>
          <div class="value">{{ $order->extra['refund_disagree_reason'] }}</div>
        </div>
        @endif
        <!-- 結帳按鈕開始 -->
        @if(!$order->paid_at && !$order->closed)
          <div class="payment-buttons">
            <a href="{{ route('payment.ecpay', ['order' => $order->id]) }}" class="btn btn-primary btn-sm">結帳</a>
          </div>
        @endif
        <!-- 結帳按鈕結束 -->
        <!-- 如果訂單的出貨狀態為已出貨則顯示確認收貨按鈕 -->
        @if($order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
        <div class="receive-button">
          <!-- 將原本的表單替換成下面這個按鈕 -->
          <button type="button" id="btn-receive" class="btn btn-sm btn-success">確認收貨</button>
        </div>
        @endif
        <!-- 訂單已支付，且退款狀態是未退款時顯示申請退款按鈕 -->
        @if($order->paid_at && $order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
        <div class="refund-button">
          <button id="btn-apply-refund" class="btn btn-sm btn-danger">申請退款</button>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
</div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
  $(document).ready(function() {
    // 確認收貨按鈕點擊事件
    $('#btn-receive').click(function() {
      // 彈出確認框
      swal.fire({
        title: '確認已經收到商品？',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '確認收到',
        confirmButtonColor: '#d33',
      })
        .then(function(result) {
          // 如果點擊取消按鈕則不做任何操作
          if (result.isDismissed) {
            return;
          }
          // 點擊確認按鈕
          if (result.isConfirmed) {
            // ajax 提交確認操作
            axios.post('{{ route('orders.received', [$order->id]) }}')
              .then(function() {
                // 刷新頁面
                location.reload();
              });
          }
        });
      });

    // 退款按鈕點擊事件
    $('#btn-apply-refund').click(function() {
      // 彈出確認框
      swal.fire({
        text: '請輸入退款理由',
        input: 'text',
      })
        .then(function(input) {
          // 當用戶點擊 swal 彈出框上的按鈕時觸發這個函數
          if (!input['value']) {
            swal.fire('退款理由不得為空', '', 'error')
            return;
          }
          // 請求退款接口
          axios.post('{{ route('orders.apply_refund', [$order->id]) }}', {reason: input['value']})
            .then(function() {
              swal.fire('申請退款成功', '', 'success').then(function() {
                // 用戶點擊彈出框上按鈕重新加載頁面
                location.reload();
              });
            });
        });
      });
  });
</script>
@endsection