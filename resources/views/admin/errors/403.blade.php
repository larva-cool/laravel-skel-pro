<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{__('Forbidden')}}</title>
    <link rel="stylesheet" href="{{asset('admin/component/pear/css/pear.css')}}"/>
    <link rel="stylesheet" href="{{asset('admin/admin/css/other/error.css')}}"/>
</head>
<body>
<div class="content">
    <img src="{{asset('admin/admin/images/403.svg')}}" alt="">
    <div class="content-r">
        <h1>403</h1>
        <p>{{__('Forbidden')}}</p>
        <button class="pear-btn pear-btn-primary">返回首页</button>
    </div>
</div>
<script src="{{asset('admin/component/layui/layui.js')}}"></script>
<script src="{{asset('admin/component/pear/pear.js')}}"></script>
</body>
</html>
