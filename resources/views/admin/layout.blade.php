<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '浏览页面')@if (request()->path() != '/')
            - {{ config('app.name', 'Laravel') }}
        @endif</title>
    <link rel="stylesheet" href="{{asset('admin/component/pear/css/pear.css')}}"/>
    <!-- 重置样式 -->
    <link rel="stylesheet" href="{{asset('admin/admin/css/reset.css')}}" />
    @stack('head')
</head>
<body>
@yield('content')
<script src="{{asset('admin/component/layui/layui.js')}}"></script>
<script src="{{asset('admin/component/pear/pear.js')}}"></script>
<script src="{{asset('admin/admin/js/permission.js')}}"></script>
<script src="{{asset('admin/admin/js/common.js')}}"></script>
@stack('scripts')
@stack('footer')
</body>
</html>
