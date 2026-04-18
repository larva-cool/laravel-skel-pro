<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>登录 - {{ config('app.name', 'Laravel') }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 样 式 文 件 -->
    <link rel="stylesheet" href="{{asset('admin/component/pear/css/pear.css')}}"/>
    <link rel="stylesheet" href="{{asset('admin/admin/css/other/login.css')}}"/>
</head>
<!-- 代 码 结 构 -->
<body background="{{asset('admin/admin/images/background.svg')}}" style="background-size: cover;">
<form class="layui-form" action="javascript:void(0);">
    @csrf
    <div class="layui-form-item">
        <img class="logo" src="{{asset('admin/admin/images/logo.png')}}"/>
        <div class="title">{{ settings('system.title') }}</div>
    </div>
    <div class="layui-form-item">
        <input type="text" placeholder="请输入登录账号" value="admin" lay-verify="required" hover name="account" class="layui-input"/>
    </div>
    <div class="layui-form-item">
        <input type="password" placeholder="请输入密码" value="password" lay-verify="required" name="password" class="layui-input"/>
    </div>
    <div class="layui-form-item">
        <input type="checkbox" name="remember" value="1" title="记住密码" lay-skin="primary" checked>
    </div>
    <div class="layui-form-item">
        <button type="button" class="pear-btn pear-btn-success login" lay-submit lay-filter="login">
            登 入
        </button>
    </div>
</form>
<!-- 资 源 引 入 -->
<script src="{{asset('admin/component/layui/layui.js')}}"></script>
<script src="{{asset('admin/component/pear/pear.js')}}"></script>
<script>
    layui.use(['form', 'button', 'popup'], function () {
        let form = layui.form;
        let button = layui.button;
        let popup = layui.popup;
        // 登 录 提 交
        form.on('submit(login)', function (data) {
            let btn = button.load({
                elem:'.login',
            })
            layui.$.ajax({
                url: '{{url('admin/auth/login')}}',
                type: "POST",
                data: data.field,
                success: function (res) {
                    btn.stop(function() {
                        popup.success(res.message, function () {
                            location.href="{{route('admin.index')}}";
                        })
                    });
                },
                error: function (xhr, status, error) {
                    btn.stop(function() {
                        popup.failure(xhr.responseJSON.message);
                    });
                },
            });
            return false;
        });
    })
</script>
</body>
</html>
