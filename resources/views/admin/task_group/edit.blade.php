@extends('admin.layout')

@section('title', '修改用户组')

@section('content')
<form class="layui-form">
    @csrf
    @method('PUT')
    <div class="mainBox">
        <div class="main-container mr-5">
            <div class="layui-form-item">
                <label class="layui-form-label required">任务组名称</label>
                <div class="layui-input-block">
                    <input type="text" name="name" value="{{$item->name}}" required lay-verify="required" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">任务组描述</label>
                <div class="layui-input-block">
                    <input type="text" name="description" value="{{$item->description}}" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">任务类型</label>
                <input name="type" value="{{$item->type->value}}" type="hidden">
                <div class="layui-form-mid">{{$item->type_name}}</div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">任务组状态</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="status" title="开启|关闭" value="1" lay-skin="switch" lay-filter="status" @checked($item->status == 1)>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label required">是否显示</label>
                <div class="layui-input-block">
                    <input type="checkbox" name="visibility" title="可见|不可见" value="1" lay-skin="switch" lay-filter="visibility" @checked($item->visibility == 1)>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-block">
                    <input type="text" name="order" value="{{$item->order}}" class="layui-input">
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
                url: "{{route('admin.task_groups.update', $item->id)}}"
                , type: "POST"
                , dataType: "json"
                , data: data.field
                , success: function(res) {
                    popup.success(res.message, function() {
                        parent.layer.close(parent.layer.getFrameIndex(window.name));
                    });
                }
                , error: function(xhr, status, error) {
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
