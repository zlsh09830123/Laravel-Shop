<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">訂單流水號：{{ $order->no }}</h3>
    <div class="box-tools">
      <div class="btn-group float-right" style="margin-right: 10px">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 列表</a>
      </div>
    </div>
  </div>
  <div class="box-body">
    <table class="table table-bordered">
      <tbody>
      <tr>
        <td>買家：</td>
        <td>{{ $order->user->name }}</td>
        <td>支付時間：</td>
        <td>{{ $order->paid_at->format('Y-m-d H:i:s') }}</td>
      </tr>
      <tr>
        <td>支付方式：</td>
        <td>{{ $order->payment_method }}</td>
        <td>支付管道編號：</td>
        <td>{{ $order->payment_no }}</td>
      </tr>
      <tr>
        <td>收件地址</td>
        <td colspan="3">{{ $order->address['address'] }} {{ $order->address['zip_code'] }} {{ $order->address['contact_name'] }} {{ $order->address['contact_phone'] }}</td>
      </tr>
      <tr>
        <td rowspan="{{ $order->items->count() + 1 }}">商品列表商品列表</td>
        <td>商品名稱</td>
        <td>單價</td>
        <td>數量</td>
      </tr>
      @foreach($order->items as $item)
      <tr>
        <td>{{ $item->product->title }} {{ $item->productSku->title }}</td>
        <td>${{ number_format($item->price) }}</td>
        <td>{{ $item->amount }}</td>
      </tr>
      @endforeach
      <tr>
        <td>訂單金額：</td>
        <td>${{ number_format($order->total_amount) }}</td>
        <!-- 這裡新增一個出貨狀態 -->
        <td>出貨狀態：</td>
        <td>{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</td>
      </tr>
      <!-- 訂單出貨開始 -->
      <!-- 如果訂單未出貨，顯示出貨表單 -->
      @if ($order->ship_status === \App\Models\Order::SHIP_STATUS_PENDING)
      <tr>
        <td colspan="4">
          <form action="{{ route('admin.orders.ship', [$order->id]) }}" method="post" class="form-inline">
						@csrf
						<div class="form-group {{ $errors->has('express_company') ? 'has-error' : '' }}">
							<label for="express_company" class="control-label">物流公司</label>
							<input type="text" id="express_company" name="express_company" value="" class="form-control" placeholder="輸入物流公司">
							@if ($errors->has('express_company'))
								@foreach ($errors->get('express_company') as $msg)
									<span class="help-block">{{ $msg }}</span>
								@endforeach
							@endif
						</div>
						<div class="form-group {{ $errors->has('express_no') ? 'has-error' : '' }}">
							<label for="express_no" class="control-label">物流單號</label>
							<input type="text" id="express_no" name="express_no" value="" class="form-control" placeholder="輸入物流單號">
							@if ($errors->has('express_no'))
								@foreach ($errors->get('express_no') as $msg)
									<span class="help-block">{{ $msg }}</span>
								@endforeach
							@endif
						</div>
						<button type="submit" class="btn btn-success" id="ship-btn">出貨</button>
					</form>
        </td>
			</tr>
			@else
			<!-- 否則顯示物流公司和物流單號 -->
			<tr>
				<td>物流公司：</td>
				<td>{{ $order->ship_data['express_company'] }}</td>
				<td>物流單號：</td>
				<td>{{ $order->ship_data['express_no'] }}</td>
			</tr>
			@endif
			<!-- 訂單出貨結束 -->
      </tbody>
    </table>
  </div>
</div>