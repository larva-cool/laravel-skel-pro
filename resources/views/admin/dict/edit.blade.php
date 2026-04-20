@extends('admin.layout')

@section('title', '字典管理')

@section('content')
    <form class="layui-form" method="POST" action="{{ route('admin.dicts.update', $item->id) }}">
        @method('PUT')
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">字典名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="{{$item->name}}" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">描述</label>
                    <div class="layui-input-block">
                        <input type="text" name="description" value="{{$item->description}}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">字典编码</label>
                    <div class="layui-input-block">
                        <input type="text" name="code" value="{{$item->code}}" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <x-forms.switcher name="status" :value="$item->status" />
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
            let popup = layui.popup;
            let $ = layui.$;
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.dicts.update', $item->id)}}",
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
