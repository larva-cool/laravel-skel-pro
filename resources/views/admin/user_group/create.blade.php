@extends('admin.layout')

@section('title', '添加用户组')

@section('content')
    <form class="layui-form" action="">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">用户组名</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label required">用户组描述</label>
                    <div class="layui-input-block">
                        <input type="text" name="desc" value="" required lay-verify="required" class="layui-input">
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
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.user_groups.store')}}",
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
