@extends('admin.layout')

@section('title', '添加用户')

@section('content')
    <form class="layui-form" method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">用户名</label>
                    <div class="layui-input-block">
                        <input type="text" name="username" value="" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">昵称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="password" value="" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">手机</label>
                    <div class="layui-input-block">
                        <input type="text" name="phone" value="" class="layui-input">
                    </div>
                </div>

            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                        lay-filter="save">
                    提交
                </button>
                <button type="reset" class="pear-btn pear-btn-md">
                    重置
                </button>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        //提交事件
        layui.use(["form", "popup"], function () {
            layui.form.on("submit(save)", function (data) {
                layui.$.post("{{route('admin.users.store')}}", data.field)
                    .then(function (data) {
                        return layui.popup.success(data.message, function () {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    })
                    .catch(function (xhr, status, error) {
                        layui.popup.failure(xhr.responseJSON.message);
                    });
                return false;
            });
        });
    </script>
@endpush
