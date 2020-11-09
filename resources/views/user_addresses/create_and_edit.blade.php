@extends('layouts.app')

@section('title', ($address->id ? '修改' : '新增') . '收件地址')

@section('content')
  <div class="row">
    <div class="col-md-10 offset-lg-1">
      <div class="card">
        <div class="card-header">
          <h2 class="text-center">
            {{ $address->id ? '修改': '新增' }}收件地址
          </h2>
        </div>
        <div class="card-body">
          <!-- 錯誤訊息開始 -->
          @if (count($errors))
          @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
          @endforeach
          @endif
          <!-- 錯誤訊息結束 -->
          <!-- inline-template 代表透過內聯方式引入 component -->
          <user-addresses-create-and-edit inline-template>
            @if($address->id)
              <form class="form-horizontal" role="form" action="{{ route('user_addresses.update', ['user_address' => $address->id]) }}" method="post">
                @method('PUT')
            @else
              <form class="form-horizontal" role="form" action="{{ route('user_addresses.store') }}" method="post">
            @endif
              @csrf
              <!-- inline-template 代表透過內聯方式引入 component -->
              <select-district :init-value="['{{ old('city', $address->city) }}', '{{ old('district', $address->district) }}']" @change="onDistrictChanged" inline-template>
                <div class="form-row">
                  <div class="form-group col-6">
                    <label>縣市</label>
                    <select class="form-control" v-model="cityId">
                      <option value="">請選擇縣市</option>
                      <option v-for="(city, index) in cities" :key="index" :value="index">@{{ city }}</option>
                    </select>
                  </div>
                  <div class="form-group col-6">
                    <label>鄉鎮市區</label>
                    <select class="form-control" v-model="districtId">
                      <option value="">請選擇鄉鎮市區</option>
                      <option v-for="(district, index) in districts" :key="index" :value="index">@{{ district }}</option>
                    </select>
                  </div>
                </div>
              </select-district>

              <input type="hidden" name="city" v-model="city">
              <input type="hidden" name="district" v-model="district">
              <input type="hidden" name="zip_code" v-model="zip_code">

              <div class="form-group">
                <label>詳細地址</label>
                <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
              </div>

              <!-- <div class="form-group">
                <label>@lang('validation.attributes.full_address')</label>
                <div>@{{ fullAddress || '無' }}</div>
              </div> -->

              <div class="form-group">
                <label>郵遞區號</label>
                <input type="text" class="form-control" name="zip_code" value="{{ old('zip_code', $address->zip_code) }}">
              </div>

              <div class="form-group">
                <label>聯絡人姓名</label>
                <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
              </div>

              <div class="form-group">
                <label>聯絡人電話</label>
                <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
              </div>

              <div class="form-group text-center">
                <button class="btn btn-primary">送出</button>
              </div>

            </form>
          <user-addresses-create-and-edit>
        </div>
      </div>
    </div>
  </div>
@endsection