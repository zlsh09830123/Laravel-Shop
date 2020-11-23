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
        <td rowspan="{{ $order->items->count() + 1 }}">商品列表</td>
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
      @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
      <tr>
        <td>退款狀態：</td>
        <td colspan="2">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}，理由：{{ $order->extra['refund_reason'] }}</td>
        <td>
          <!-- 如果訂單退款狀態是已申請，則顯示處理按鈕 -->
          @if($order->refund_status === \App\Models\Order::REFUND_STATUS_APPLIED)
          <button class="btn btn-sm btn-success" id="btn-refund-agree">同意</button>
          <button class="btn btn-sm btn-danger" id="btn-refund-disagree">不同意</button>
          @endif
        </td>
      </tr>
      @endif
      </tbody>
    </table>
  </div>
</div>

<script>
$(document).ready(function() {
  // 不同意按鈕的點擊事件
  $('#btn-refund-disagree').click(function() {
    // Laravel-Admin 使用的 SweetAlert2 版本與我們在前台使用的版本不一樣
    swal.fire({
      title: '輸入拒絕退款理由',
      input: 'text',
      showCancelButton: true,
      confirmButtonText: '確認',
      cancelButtonText: '取消',
      showLoaderOnConfirm: true,
      preConfirm: function(inputValue) {
        if (!inputValue) {
          swal.fire('理由不能為空', '', 'error');
          return false;
        }
        // Laravel-Admin 沒有 axios，使用 jQuery 的 ajax 方法來請求
        return $.ajax({
          url: '{{ route('admin.orders.handle_refund', [$order->id]) }}',
          type: 'POST',
          data: JSON.stringify({ // 將請求 JSON 字串化
            agree: false, // 拒絕申請
            reason: inputValue,
            // 帶上 CSRF Token
            // Laravel-Admin 頁面裡可以透過 LA.token 獲得 CSRF Token
            _token: LA.token,
          }),
          contentType: 'application/json', // 請求的資料格式為 JSON
        });
      },
      allowOutsideClick: false
    }).then(function(result) {
      // 如果用戶點擊取消按鈕，則不做任何操作
      if (result.isDismissed) {
        return;
      }
      swal.fire('操作成功', '', 'success').then(function() {
        // 用戶點擊彈出框的按鈕時刷新頁面
        location.reload();
      });
    });
  });
});
</script>