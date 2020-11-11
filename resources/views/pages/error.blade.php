@extends('layouts.app')

@section('title', '錯誤')

@section('content')
<div class="card">
    <div class="card-header">錯誤</div>
    <div class="card-body text-center">
        <h1>{{ $msg }}</h1>
        <a class="btn btn-primary" href="{{ route('root') }}">返回首頁</a>
    </div>
</div>
@endsection