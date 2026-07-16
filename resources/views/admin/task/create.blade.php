@extends('admin.layout')

@section('title', '添加任务')

@section('content')
<form class="layui-form" action="">
    @csrf
    <div class="mainBox">
        <div class="main-container mr-5">
            <div class="layui-form-item">
                <label class="layui-form-label required">任务名称</label>
                <div class="layui-input-block">
                    <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">任务类型</label>
                <input name="type" value="{{$group->type}}" type="hidden">
                <div class="layui-form-mid">{{$group->type_name}}</div>
            </div>

            <div class="layui-form-item" id="task_watch_video" @if($group->type != 'watch_playlet') style="display: none;" @endif>
                <label class="layui-form-label required">观看视频时长（秒）</label>
                <div class="layui-input-block">
                    <input type="text" name="condition[played_time]" value="0" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item" id="task_sign_in" @if($group->type != 'sign_in') style="display: none;" @endif>
                <label class="layui-form-label required">连续签到天数</label>
                <div class="layui-input-block">
                    <input type="text" name="condition[serial_days]" value="0" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">奖励金币</label>
                <div class="layui-input-block">
                    <input type="text" name="coins" value="" required lay-verify="required" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">活跃度奖励</label>
                <div class="layui-input-block">
                    <x-forms.switcher name="activity_bonus"/>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-block">
                    <input type="text" name="order" value="0" class="layui-input">
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
            $.ajax({
                url: "{{$store_url}}",
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
