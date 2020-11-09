<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'district',
        'address',
        'zip_code',
        'contact_name',
        'contact_phone',
        'last_used_at'
    ];
    protected $dates = ['last_used_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getFullAddressAttribute()
    {
        return "{$this->zip_code} {$this->city}{$this->district}{$this->address}";
    }
}
