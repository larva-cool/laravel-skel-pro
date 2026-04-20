@extends('admin.layout')

@section('title', '添加地区')

@section('content')
    <style>
        .layui-iconpicker .layui-anim {
            bottom: 42px !important;
            top: inherit !important;
        }
    </style>
    <form class="layui-form" action="">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label">父地区</label>
                    <div class="layui-input-block">
                        <div name="parent_id" id="parent_id" value=""></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label required">地区名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" required lay-verify="required" value="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">城市区号</label>
                    <div class="layui-input-block">
                        <input type="text" name="city_code" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">地区编码</label>
                    <div class="layui-input-block">
                        <input type="text" name="area_code" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-block">
                        <input type="number" name="order" value="0" class="layui-input">
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
        // 上级菜单
        layui.use(["form", "popup", "xmSelect", "popup"], function () {
            let $ = layui.$;
            let form = layui.form;
            let xmSelect = layui.xmSelect;
            let popup = layui.popup;
            $.ajax({
                url: "{{route('admin.areas.select')}}",
                dataType: "json",
                success: function (res) {
                    let value = $("#parent_id").attr("value");
                    xmSelect.render({
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
            // 表单提交事件
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.areas.store')}}",
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
