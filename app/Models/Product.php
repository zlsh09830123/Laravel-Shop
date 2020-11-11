<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'on_sale',
        'rating',
        'sold_count',
        'review_count',
        'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一個布林類型的欄位
    ];

    public function skus()
    {
        return $this->hasMany('App\Models\ProductSku');
    }

    public function getImageUrlAttribute()
    {
        // 如果 image 欄位本身就已經是完整的 url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }
        return \Storage::disk('public')->url($this->attributes['image']);
    }
}
