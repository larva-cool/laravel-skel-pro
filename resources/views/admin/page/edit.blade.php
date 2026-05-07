@extends('admin.layout')

@section('title', '修改页面')

@section('content')
<form class="layui-form">
    @csrf
    @method('PUT')
    <div class="mainBox">
        <div class="main-container mr-5">
            <div class="layui-form-item">
                <label class="layui-form-label required">标签名称</label>
                <div class="layui-input-block">
                    <x-forms.input name="title" value="{{$item->title}}" required></x-forms.input>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">描述</label>
                <div class="layui-input-block">
                    <x-forms.input name="desc" value="{{$item->desc}}" class="layui-input"></x-forms.input>
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">内容</label>
                <div class="layui-input-block">
                    <x-forms.editor name="content" :value="$item->content"/>
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
                url: "{{route('admin.pages.update', $item->id)}}"
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
