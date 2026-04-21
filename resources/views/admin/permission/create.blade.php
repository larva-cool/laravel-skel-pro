@extends('admin.layout')

@section('title', '权限管理')

@section('content')
    <form class="layui-form" action="">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">权限名</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">权限标识</label>
                    <div class="layui-input-block">
                        <input type="text" name="slug" value="" required lay-verify="required"
                            class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">HTTP方法</label>
                    <div class="layui-input-block">
                        <div name="http_method" id="http_method" value=""></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">HTTP路径</label>
                    <div class="layui-input-block">
                        <div name="http_path" id="http_path" value=""></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">排序</label>
                    <div class="layui-input-block">
                        <input type="text" name="order" value="0" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">菜单</label>
                    <div class="layui-input-block">
                        <div name="menus" id="menus" value=""></div>
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
        layui.use(["form", "xmSelect", "popup"], function() {
            let $ = layui.$;
            let xmSelect = layui.xmSelect;
            let popup = layui.popup;
            let form = layui.form;

            let http_method_value = $("#http_method").attr("value");
            let http_path_value = $("#http_path").attr("value");
            let menus_value = $("#menus").attr("value");

            xmSelect.render({
                el: "#http_method",
                name: "http_method",
                initValue: http_method_value ? http_method_value.split(",") : [],
                autoRow: true,
                data: [
                    {name: 'GET', value: 'GET'},
                    {name: 'POST', value: 'POST'},
                    {name: 'PUT', value: 'PUT'},
                    {name: 'DELETE', value: 'DELETE'},
                    {name: 'PATCH', value: 'PATCH'},
                    {name: 'OPTIONS', value: 'OPTIONS'},
                    {name: 'HEAD', value: 'HEAD'},
                ],
                toolbar: {
                    show: true,
                    list: ["ALL", "CLEAR", "REVERSE"]
                },
            });

            $.ajax({
                url: "{{ route('admin.routes') }}",
                dataType: "json",
                success: function(res) {
                    xmSelect.render({
                        el: "#http_path",
                        name: "http_path",
                        initValue: http_path_value ? http_path_value.split(",") : [],
                        data: res,
                        autoRow: true,
                        toolbar: {
                            show: true,
                            list: ["ALL", "CLEAR", "REVERSE"]
                        },
                    })
                }
            });

            $.ajax({
                url: "{{ route('admin.ajax.menu-select') }}",
                dataType: "json",
                success: function(res) {
                    xmSelect.render({
                        el: "#menus",
                        name: "menus",
                        initValue: menus_value ? menus_value.split(",") : [],
                        data: res,
                        autoRow: true,
                        tree: {
                            "show": true,
                            strict: false,
                            expandedKeys: true,
                        },
                        toolbar: {
                            show: true,
                            list: ["ALL", "CLEAR", "REVERSE"]
                        },
                    })
                }
            });

            //提交事件
            form.on("submit(save)", function(data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{ route('admin.permissions.store') }}",
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
