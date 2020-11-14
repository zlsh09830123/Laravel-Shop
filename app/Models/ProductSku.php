<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InternalException;

class ProductSku extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'stock'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('減庫存不可少於零');
        }
        // 會返回影響的行數，可透過影響行數判斷減操作庫存是否成功，若不成功說明商品庫存不足
        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加庫存不可少於零');
        }
        $this->increment('stock', $amount);
    }
}
