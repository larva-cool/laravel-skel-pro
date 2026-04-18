@extends('admin.layout')

@section('title', '用户管理')

@section('content')
    <form class="layui-form" method="POST">
        @method('PUT')
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">角色</label>
                    <div class="layui-input-block">
                        <div name="roles" id="roles" value="{{ $item->roles->pluck('id')->implode(',') }}"></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">用户名</label>
                    <div class="layui-input-block">
                        <x-forms.input name="username" value="{{ $item->username }}" required />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">昵称</label>
                    <div class="layui-input-block">
                        <x-forms.input name="name" value="{{ $item->name }}" required />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="password" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">邮箱</label>
                    <div class="layui-input-block">
                        <x-forms.input name="email" value="{{ $item->email }}" required />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">手机</label>
                    <div class="layui-input-block">
                        <x-forms.input name="phone" value="{{ $item->phone }}" required />
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                            lay-filter="save">
                            提交
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md">
                            重置
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </form>
@endsection

@push('scripts')
    <script>
        // 字段 角色 roles
        layui.use(["form", "popup", "xmSelect", "popup"], function() {
            let $ = layui.$;
            let xmSelect = layui.xmSelect;
            let form = layui.form;
            let popup = layui.popup;
            $.ajax({
                url: "{{ route('admin.roles.select') }}",
                type: "GET",
                dataType: "json",
                success: function(res) {
                    let value = layui.$("#roles").attr("value");
                    let initValue = value ? value.split(",") : [];
                    if (!top.Admin.Account.isSuperAdmin) {
                        layui.each(res.data, function(k, v) {
                            v.disabled = true;
                        });
                    }
                    xmSelect.render({
                        el: "#roles",
                        name: "roles",
                        tips: '请选择角色',
                        initValue: initValue,
                        empty: '呀, 没有数据呢',
                        height: 'auto',
                        data: res,
                        layVerify: "required",
                        toolbar: {
                            show: true,
                            list: ["ALL", "CLEAR", "REVERSE"]
                        },
                    });
                },
                error: function(xhr, status, error) {
                    popup.failure(xhr.responseJSON.message);
                }
            });

            //提交事件
            form.on("submit(save)", function(data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{ route('admin.admins.update', $item->id) }}",
                    type: "POST",
                    dataType: "json",
                    data: data.field,
                    success: function(res) {
                        popup.success(res.message, function() {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },
                    error: function(xhr, status, error) {
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
