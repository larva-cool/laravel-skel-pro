@extends('admin.layout')

@section('title', '系统配置')

@section('content')
    <style>
        .layui-form-label {
            width: 120px;
        }

        .layui-input-block input {
            width: 500px;
        }

        .layui-form-item .layui-input-inline {
            width: 350px;
        }
    </style>
    <div class="pear-container">
        <div class="layui-card">
            <div class="layui-card-body">
                <div class="layui-tabs">
                    <ul class="layui-tabs-header">
                        <li lay-id="baseInfo">基本信息</li>
                        <li lay-id="password">安全设置</li>
                    </ul>
                    <div class="layui-tabs-body">
                        <!--基本信息-->
                        <div class="layui-tabs-item" lay-id="baseInfo">
                            <form class="layui-form" lay-filter="baseInfo">
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">昵称</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="name"
                                               value="{{$admin->name ?? ''}}"
                                               placeholder="请输入昵称" required
                                               lay-verify="required" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">邮箱</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="email"
                                               value="{{$admin->email ?? ''}}" required
                                               lay-verify="required"
                                               placeholder="请输入邮箱" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">手机</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="phone" required
                                               lay-verify="required"
                                               value="{{$admin->phone ?? ''}}" placeholder="请输入手机号"
                                               class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">头像</label>
                                    <div class="layui-input-block">
                                        <div style="display: flex; align-items: center;">
                                            <div class="layui-upload-drag" style="margin-right: 20px;">
                                                <img id="avatar_show" src="{{ $admin->avatar }}" alt="上传图片" width="90"
                                                     height="90">
                                                <input type="text" style="display:none" id="avatar_upimage" name="avatar" value="{{ $admin->avatar }}"/>
                                            </div>
                                            <div style="display: flex; flex-direction: column;">
                                                <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" id="avatar"><i
                                                        class="layui-icon layui-icon-upload"></i>上传图片
                                                </button>
                                                <div class="layui-form-mid layui-word-aux" style="margin-top: 10px;">建议上传尺寸450x450</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block">
                                        <button type="submit" class="pear-btn pear-btn-primary pear-btn-md"
                                                lay-submit=""
                                                lay-filter="saveBaseInfo">提交</button>
                                        <button type="reset" class="pear-btn pear-btn-md">
                                            重置
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--安全设置-->
                        <div class="layui-tabs-item" lay-id="password">
                            <form class="layui-form">
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">旧密码</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="old_password"
                                               value="" required lay-verify="required"
                                               placeholder="请输入旧密码" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">新密码</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="new_password"
                                               value="" required lay-verify="required"
                                               placeholder="请输入新密码" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">确认新密码</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="new_password_confirm"
                                               value="" required lay-verify="required"
                                               placeholder="请确认新密码" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block">
                                        <button type="submit" class="pear-btn pear-btn-primary pear-btn-md"
                                                lay-submit=""
                                                lay-filter="savePassword">提交</button>
                                        <button type="reset" class="pear-btn pear-btn-md">
                                            重置
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // 字段 头像 avatar
        layui.use(["form", "popup","upload", "layer", 'croppers'], function () {
            let croppers = layui.croppers;
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            //创建一个头像上传组件
            croppers.render({
                elem: '#avatar'
                , saveW: 450     //保存宽度
                , saveH: 450
                , mark: 1/1    //选取比例
                , area: ['750px', '500px']  //弹窗宽度
                , url: "{{route('admin.admins.avatar', $admin->id)}}"
                , done: function (res) {
                    //上传完毕回调
                    layui.$('#avatar_upimage').val(res.file_path);
                    layui.$('#avatar_show').attr('src', res.file_url);
                }
            });

            //提交事件
            form.on("submit(saveBaseInfo)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.admins.update_person')}}",
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

            form.on("submit(savePassword)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.admins.update_password')}}",
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
