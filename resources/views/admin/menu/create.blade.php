@extends('admin.layout')

@section('title', '添加菜单')

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
                    <label class="layui-form-label">上级菜单</label>
                    <div class="layui-input-block">
                        <div name="parent_id" id="parent_id" value=""></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" required lay-verify="required" value=""
                            class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">Url</label>
                    <div class="layui-input-block">
                        <input type="text" name="href" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <input name="icon" id="icon" />
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <input type="radio" name="type" value="0" title="目录">
                        <input type="radio" name="type" value="1" title="菜单" checked>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-block">
                        <input type="number" name="order" value="0" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">角色</label>
                    <div class="layui-input-block">
                        <div name="roles" id="roles" value=""></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">权限</label>
                    <div class="layui-input-block">
                        <div name="permissions" id="permissions" value=""></div>
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
        // 上级菜单
        layui.use(["jquery", "iconPicker", "xmSelect", "popup"], function() {
            let $ = layui.$;
            // 图标选择
            layui.iconPicker.render({
                elem: "#icon",
                type: "fontClass",
                page: false,
            });
            $.ajax({
                url: "{{ route('admin.ajax.menu-select') }}",
                dataType: "json",
                success: function(res) {
                    let value = layui.$("#parent_id").attr("value");
                    layui.xmSelect.render({
                        el: "#parent_id",
                        name: "parent_id",
                        initValue: [value],
                        tips: "无",
                        toolbar: {
                            show: true,
                            list: ["CLEAR"]
                        },
                        data: res,
                        model: {
                            "icon": "hidden",
                            "label": {
                                "type": "text"
                            }
                        },
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

            $.ajax({
                url: "{{ route('admin.ajax.role-select') }}",
                dataType: "json",
                success: function(res) {
                    let value = $("#roles").attr("value");
                    let initValue = value ? value.split(",") : [];
                    layui.xmSelect.render({
                        el: "#roles",
                        name: "roles",
                        initValue: initValue,
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
        });

        // 表单提交事件
        layui.use(["form", "popup"], function() {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on("submit(save)", function(data) {
                let loading = layer.load();
                data.field.roles = layui.xmSelect.getValue('roles');
                $.ajax({
                    url: "{{ route('admin.menus.store') }}",
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
