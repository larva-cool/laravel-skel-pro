@extends('admin.layout')

@section('title', '角色管理')

@section('content')
    <form class="layui-form">
        @csrf
        @method('PUT')
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">权限名</label>
                    <div class="layui-input-block">
                        <input type="text" name="display_name" value="{{ $item->display_name }}" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">权限标识</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="{{ $item->name }}" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">Guard</label>
                    <div class="layui-input-block">
                        <input type="text" name="guard_name" value="{{ $item->guard_name }}" required lay-verify="required" class="layui-input">
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
        // 字段 权限 rules
        layui.use(["form", "popup"], function() {
            let $ = layui.$;
            let form = layui.form;
            let popup = layui.popup;

            form.on("submit(save)", function(data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{ route('admin.permissions.update', $item->id) }}",
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
