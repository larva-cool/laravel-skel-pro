@extends('admin.layout')

@section('title', '用户管理')

@section('content')
    <form class="layui-form" method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">角色</label>
                    <div class="layui-input-block">
                        <div name="roles" id="roles" value=""></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">用户名</label>
                    <div class="layui-input-block">
                        <x-forms.input name="username" value="" required lay-verify="required" class="layui-input"/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">昵称</label>
                    <div class="layui-input-block">
                        <x-forms.input name="name" value="" required lay-verify="required" class="layui-input"/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">密码</label>
                    <div class="layui-input-block">
                        <x-forms.input name="password" value="" required lay-verify="required" class="layui-input"/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">邮箱</label>
                    <div class="layui-input-block">
                        <x-forms.input name="email" value="" required/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">手机</label>
                    <div class="layui-input-block">
                        <x-forms.input name="phone" value="" required/>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
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
        // 字段 角色 roles
        layui.use(["jquery", "xmSelect", "form", "popup"], function () {
            let $ = layui.$;
            let xmSelect = layui.xmSelect;
            let form = layui.form;
            let popup = layui.popup;

            $.ajax({
                url: "{{route('admin.roles.select')}}",
                type: "GET",
                dataType: "json",
                success: function (res) {
                    xmSelect.render({
                        el: "#roles",
                        name: "roles",
                        tips: '请选择角色',
                        empty: '呀, 没有数据呢',
                        height: 'auto',
                        data: res,
                        layVerify: "required",
                        toolbar: {show: true, list: ["ALL", "CLEAR", "REVERSE"]},
                    });
                },
                error: function (xhr, status, error) {
                    popup.failure(xhr.responseJSON.message);
                }
            });

            //提交事件
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.admins.store')}}",
                    type: "POST",
                    dataType: "json",
                    data: data.field,
                    success: function (res) {
                        popup.success(res.message, function () {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },
                    error: function (xhr, status, error) {
                        popup.failure(xhr.responseJSON.message);
                    },
                    complete: function() {
                        layer.close(loading);
                    }
                });

                return false;
            });
        });


    </script>

@endpush
