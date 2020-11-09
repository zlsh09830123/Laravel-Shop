<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
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
            'city'          => 'required',
            'district'      => 'required',
            'address'       => 'required',
            'zip_code'       => 'required',
            'contact_name'  => 'required',
            'contact_phone' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'city'          => '城市',
            'district'      => '地區',
            'address'       => '詳細地址',
            'zip_code'       => '郵遞區號',
            'contact_name'  => '聯絡人姓名',
            'contact_phone' => '聯絡人電話',
        ];
    }
}
