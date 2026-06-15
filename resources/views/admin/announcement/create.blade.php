@extends('admin.layout')

@section('title', '添加公告')

@section('content')
    <form class="layui-form" action="">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label">覆盖范围</label>
                    <div class="layui-input-block">
                        <x-forms.checkbox name="coverage" :items="$coverage_options" />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">标题</label>
                    <div class="layui-input-block">
                        <x-forms.input name="title" value="" required />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">内容</label>
                    <div class="layui-input-block">
                        <x-forms.editor name="content" />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">时间</label>
                    <div class="layui-input-block">
                        <x-forms.radio name="effective_time_type" :items="$effective_time_type_options" />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">生效时间</label>
                    <div class="layui-input-block">
                        <x-forms.datetimerange name="effective" />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <x-forms.switcher name="status" />
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
        //提交事件
        layui.use(["form", "popup"], function() {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;

            form.on("submit(save)", function(data) {
                let loading = layer.load();
                data.field.content = window.contentBody;
                $.ajax({
                    url: "{{ route('admin.announcements.store') }}",
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
