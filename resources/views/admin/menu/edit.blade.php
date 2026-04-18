@extends('admin.layout')

@section('title', '修改菜单')

@section('content')
    <style>
        .layui-iconpicker .layui-anim {
            bottom: 42px !important;
            top: inherit !important;
        }
    </style>
    <form class="layui-form" method="POST">
        @csrf
        @method('PUT')
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" required lay-verify="required" value="{{$item->title}}"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">标识</label>
                    <div class="layui-input-block">
                        <input type="text" name="key" required lay-verify="required" value="{{$item->key}}"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">上级菜单</label>
                    <div class="layui-input-block">
                        <div name="parent_id" id="parent_id" value="{{$item->parent_id}}"></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">Url</label>
                    <div class="layui-input-block">
                        <input type="text" name="href" value="{{$item->href}}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <input name="icon" id="icon" value="{{$item->icon}}"/>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <input type="radio" name="type" value="0" title="目录" @checked($item->type == 0)>
                        <input type="radio" name="type" value="1" title="菜单" @checked($item->type == 1)>
                        <input type="radio" name="type" value="2" title="权限" @checked($item->type == 2)>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-block">
                        <input type="number" name="order" value="{{$item->order}}" class="layui-input">
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
        // 图标选择
        layui.use(["iconPicker"], function () {
            layui.iconPicker.render({
                elem: "#icon",
                type: "fontClass",
                page: false,
                value: "{{$item->icon}}"
            });
        });
        // 上级菜单
        layui.use(["jquery", "xmSelect", "popup"], function () {
            layui.$.ajax({
                url: "{{route('admin.menus.select')}}",
                dataType: "json",
                success: function (res) {
                    let value = layui.$("#parent_id").attr("value");
                    layui.xmSelect.render({
                        el: "#parent_id",
                        name: "parent_id",
                        initValue: [value],
                        tips: "无",
                        toolbar: {show: true, list: ["CLEAR"]},
                        data: res,
                        model: {"icon": "hidden", "label": {"type": "text"}},
                        radio: true,
                        clickClose: true,
                        tree: {
                            show: true,
                            showFolderIcon: true,
                            showLine: true,
                            expandedKeys: true,
                            clickExpand: false,
                            clickCheck: true,
                            strict: false
                        },
                    });
                }
            });
        });
        // 提交事件
        layui.use(["form", "popup"], function () {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.menus.update', $item)}}",
                    type: "PUT",
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
