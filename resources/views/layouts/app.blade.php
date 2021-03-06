<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Shop') - Laravel Shop</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="{{URL::asset('img/favicon-32x32.png')}}">
</head>
<body>
    <div id="app" class="{{ route_class() }}-page">
        @include('layouts._header')
        <div class="container">
            @yield('content')
        </div>
        @include('layouts._footer')
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scriptsAfterJs')
</body>
</html>