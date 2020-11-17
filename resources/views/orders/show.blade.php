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
      </div>
      <div class="order-summary text-right">
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
        <!-- 結帳按鈕開始 -->
        @if(!$order->paid_at && !$order->closed)
          <div class="payment-buttons">
            <a href="{{ route('payment.ecpay', ['order' => $order->id]) }}" class="btn btn-primary btn-sm">結帳</a>
          </div>
        @endif
        <!-- 結帳按鈕結束 -->
      </div>
    </div>
  </div>
</div>
</div>
</div>
@endsection