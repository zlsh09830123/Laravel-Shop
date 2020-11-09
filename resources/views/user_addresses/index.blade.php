@extends('layouts.app')

@section('title', '收件地址列表')

@section('content')
  <div class="row">
    <div class="col-md-10 offset-md-1">
      <div class="card panel-default">
        <div class="card-header">
          收件地址列表
          <a href="{{ route('user_addresses.create') }}" class="float-right">新增收件地址</a>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>收件人</th>
              <th>收件地址</th>
              <th>電話</th>
              <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($addresses as $address)
              <tr>
                <td>{{ $address->contact_name }}</td>
                <td>{{ $address->full_address }}</td>
                <td>{{ $address->contact_phone }}</td>
                <td>
                  <a href="{{ route('user_addresses.edit', ['user_address' => $address->id]) }}" class="btn btn-primary">修改</a>
                  <form action="{{ route('user_addresses.destroy', ['user_address' => $address->id]) }}" method="post" class="d-inline-block" onsubmit="return confirm('確認刪除該收件地址?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">刪除</button>
                  </form>
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