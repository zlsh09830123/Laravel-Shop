@extends('layouts.app')

@section('title', '購物車')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-header">我的購物車</div>
  <div class="card-body">
    <table class="table table-striped">
      <thead>
      <tr>
        <th><input type="checkbox" id="select-all"></th>
        <th>商品資訊</th>
        <th>單價</th>
        <th>數量</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody class="product_list">
      @foreach($cartItems as $item)
        <tr data-id="{{ $item->productSku->id }}">
          <td>
            <input type="checkbox" name="select" value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
          </td>
          <td class="product_info">
            <div class="preview">
              <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">
                <img src="{{ $item->productSku->product->image_url }}">
              </a>
            </div>
            <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
              <span class="product_title">
                <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->title }}</a>
              </span>
              <span class="sku_title">{{ $item->productSku->title }}</span>
              @if(!$item->productSku->product->on_sale)
                <span class="warning">此商品已下架</span>
              @endif
            </div>
          </td>
          <td><span class="price">${{ $item->productSku->price }}</span></td>
          <td>
            <input type="text" class="form-control form-control-sm amount" @if(!$item->productSku->product->on_sale) disabled @endif name="amount" value="{{ $item->amount }}">
          </td>
          <td>
            <button class="btn btn-sm btn-danger btn-remove">移除</button>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <!-- 收件地址選擇及備註 -->
    <div>
      <form class="form-horizontal" role="form" id="order-form">
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">選擇收件地址</label>
          <div class="col-sm-9 col-md-7">
            <select name="address" class="form-control">
              @foreach($addresses as $address)
                <option value="{{ $address->id }}">{{ $address->full_address }} {{ $address->contact_name }} {{ $address->contact_phone }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">備註</label>
          <div class="col-sm-9 col-md-7">
            <textarea name="remark" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="offset-sm-3 col-sm-3">
            <button type="button" class="btn btn-primary btn-create-order">提交訂單</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
	$(document).ready(function() {
		// 監聽移除按鈕的點擊事件
		$('.btn-remove').click(function() {
			// $(this) 可以獲取到當前點擊的移除按鈕的 jQeury 對象
			// closest() 方法可以獲取到符合選擇器的第一個祖先元素，在這裡就是當前點擊的移除按鈕之上的 <tr> 標籤
			// data('id') 方法可以得到之前設置的 data-id 屬性的值，也就是對應的 SKU id
			var id = $(this).closest('tr').data('id');
			swal.fire({
				title: "確定要移除此商品？",
				icon: "warning",
				buttons: ['取消', '確定'],
				dangerMode: true,
			}).then(function(willDelete) {
				// 用戶點擊確定按鈕，willDelete 的值為 true，否則為 false
				if (!willDelete) {
					return;
				}
        axios.delete('/cart/' + id)
          .then(function() {
            location.reload();
          });
			});
		});

		// 監聽全選/取消全選單選框的變更事件
		$('#select-all').change(function() {
			// 獲取單選框的選中狀態
			// prop() 方法可以知道標籤中是否包含某個屬性，當單選框被勾選時，對應的標籤就會被新增一個 checked 屬性
			var checked = $(this).prop('checked');
			// 獲取所有 name=select 並不帶有 disabled 屬性的勾選框
			// 對於已下架的商品我們不希望對應的勾選框會被選中，因此需要加上 :not([disabled]) 這個條件
			$('input[name=select][type=checkbox]:not([disabled])').each(function() {
				// 將其勾選狀態設為與目標單選框一致
				$(this).prop('checked', checked);
			});
		});

    // 監聽建立訂單按鈕的點擊事件
    $('.btn-create-order').click(function() {
      // 建構請求參數，將用戶選擇的地址的 id 和備註內容寫入請求參數
      var req = {
        address_id: $('#order-form').find('select[name=address]').val(),
        items: [],
        remark: $('#order-form').find('textarea[name=remark]').val(),
      };
      // 遍歷 <table> 標籤內所有帶有 data-id 屬性的 <tr> 標籤，也就是每一個購物車中的商品 SKU
      $('table tr[data-id]').each(function() {
        // 獲取當前行的單選框
        var $checkbox = $(this).find('input[name=select][type=checkbox]');
        // 如果單選框被禁用或者沒有被勾選則跳過
        if ($checkbox.prop('disabled') || !$checkbox.prop('checked')) {
          return;
        }
        // 獲取當前行中數量輸入框
        var $input = $(this).find('input[name=amount]');
        // 如果用戶將數量設為 0 或者不是一個數字則跳過
        if ($input.val() == 0 || isNaN($input.val())) {
          return;
        }
        // 把 SKU id 和數量存入請求參數陣列中
        req.items.push({
          sku_id: $(this).data('id'),
          amount: $input.val(),
        });
      });
      axios.post('{{ route('orders.store') }}', req)
        .then(function(response) {
          swal.fire('訂單提交成功', '', 'success');
        }, function(error) {
          if (error.response.status === 422) {
            // HTTP 狀態碼為 422 代表用戶輸入驗證失敗
            var html = '<div>';
            _.each(error.response.data.errors, function(errors) {
              _.each(errors, function(error) {
                html += error + '<br>';
              });
            });
            html += '</div>';
            swal.fire({content: $(html)[0], icon: 'error'});
          } else {
            // 其他狀況為系統故障
            swal.fire('系統錯誤', '', 'error');
          }
        });
    });
	});
</script>
@endsection