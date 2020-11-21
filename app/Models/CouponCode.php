<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\CouponCodeUnavailableException;

class CouponCode extends Model
{
    use HasFactory;
    use DefaultDatetimeFormat;

    // 以常量的方式定義支持的優惠券類型
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED => '固定金額',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // 指明這兩個欄位是日期類型
    protected $dates = ['not_before', 'not_after'];

    protected $appends = ['description'];

    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一個指定長度的隨機字串，並轉成大寫
            $code = strtoupper(Str::random($length));
        // 如果生成的優惠碼已存在就繼續循環
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '滿$' . $this->min_amount;
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '優惠' . $this->value . '%';
        }

        return $str . '減$' . $this->value;
    }

    public function checkAvailable($orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('優惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('此優惠券數量已被使用完畢');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('此優惠券現在還不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('此優惠券已過期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('訂單金額低於此優惠券最低金額門檻');
        }
    }

    public function getAdjustedPrice($orderAmount)
    {
        // 固定金額
        if ($this->type === self::TYPE_FIXED) {
            // 為了保證系統強健性，我們需要訂單金額最少為 1 元
            return max(1, $orderAmount - $this->value);
        }

        return floor($orderAmount * (100 - $this->value) / 100); // 無條件捨去小數點
    }

    public function changeUsed($increase = true)
    {
        // 傳入 true 代表新增用量，否則是減少用量
        if ($increase) {
            // 與檢查 SKU 庫存類似，這裡需要檢查當前用量是否已經超過總量
            return $this->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
