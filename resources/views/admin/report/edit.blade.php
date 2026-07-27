@extends('admin.layout')

@section('title', '处理举报')

@section('content')
    <form class="layui-form" style="padding: 20px;">
        @csrf
        @method('PUT')
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label">举报人</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->user->name ?? '' }}（ID：{{ $item->user_id }}）" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">被举报对象</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->reportable_type }} #{{ $item->reportable_id }}" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">举报原因</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->reason->label() }}" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">补充说明</label>
                    <div class="layui-input-block">
                        <textarea class="layui-textarea" rows="4" readonly>{{ $item->content }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">状态</label>
                    <div class="layui-input-block">
                        <select name="status" required lay-verify="required">
                            @foreach($status_options as $value => $label)
                                <option value="{{ $value }}" @selected($item->status->value === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">处理备注</label>
                    <div class="layui-input-block">
                        <textarea name="remark" class="layui-textarea" rows="5" placeholder="请填写处理备注">{{ $item->remark }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit lay-filter="save">
                    提交
                </button>
                <button type="reset" class="pear-btn pear-btn-md">重置</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        layui.use(['form', 'popup'], function() {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on('submit(save)', function(data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{ $update_url }}",
                    type: 'POST',
                    dataType: 'json',
                    data: data.field,
                    success: function(res) {
                        popup.success(res.message, function() {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },
                    error: function(xhr) {
                        popup.failure(xhr.responseJSON ? xhr.responseJSON.message : '请求失败');
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
