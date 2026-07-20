@extends('admin.layout')

@section('title', '处理反馈')

@section('content')
    <form class="layui-form" style="padding: 20px;">
        @csrf
        @method('PUT')
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label">提交用户</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->user->name ?? '' }}（ID：{{ $item->user_id }}）" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->type->label() }}" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">标题</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->title }}" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">反馈内容</label>
                    <div class="layui-input-block">
                        <textarea class="layui-textarea" rows="6" readonly>{{ $item->content }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">联系方式</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" value="{{ $item->contact }}" readonly>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">回复内容</label>
                    <div class="layui-input-block">
                        <textarea name="reply" class="layui-textarea" rows="5" required lay-verify="required" placeholder="请填写回复内容">{{ $item->reply }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <select name="status">
                            @foreach($status_options as $value => $label)
                                <option value="{{ $value }}" @selected($item->status->value === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
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
