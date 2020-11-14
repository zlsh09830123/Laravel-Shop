<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductSku;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 判斷用戶提交的地址 ID 是否存在於資料庫並且屬於當前用戶
            // 後者條件非常重要，否則惡意用戶可以用不同的地址 ID 不斷提交訂單來遍歷出平台所有用戶的收貨地址
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'items' => ['required', 'array'],
            'items.*.sku_id' => [ // 檢查 items 陣列下每一個子陣列的 sku_id 參數
                'required',
                function ($attribute, $value, $fail) { // 對子陣列的 rule
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('此商品不存在');
                    }
                    if (!$sku->product->on_sale) {
                        return $fail('此商品未上架');
                    }
                    if ($sku->stock === 0) {
                        return $fail('此商品已售完');
                    }
                    // 獲取當前索引
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index = $m[1];
                    // 根據索引找到用戶所提交的購買數量
                    $amount = $this->input('items')[$index]['amount'];
                    if ($amount > 0 && $amount > $sku->stock) {
                        return $fail('此商品庫存不足');
                    }
                },
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
