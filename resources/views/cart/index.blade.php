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
				axios.delete('/cart/' + id).then(function() {
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
	});
</script>
@endsection