@extends('admin.layout')

@section('title', '用户管理')

@section('content')
    <div class="pear-container">
        <!-- 顶部查询表单 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form top-search-from">

                    <div class="layui-form-item">
                        <label class="layui-form-label">用户ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" value="" class="layui-input" placeholder="请输入用户ID">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">关键词</label>
                        <div class="layui-input-block">
                            <input type="text" name="keyword" value="" class="layui-input"
                                   placeholder="请输入搜索账号、邮箱、手机号">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">登录时间</label>
                        <div class="layui-input-block">
                            <div class="layui-input-block" id="created_at">
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-start"
                                       class="layui-input inline-block" placeholder="开始时间">
                                -
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-end"
                                       class="layui-input inline-block" placeholder="结束时间">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">注册时间</label>
                        <div class="layui-input-block">
                            <div class="layui-input-block" id="created_at">
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-start"
                                       class="layui-input inline-block" placeholder="开始时间">
                                -
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-end"
                                       class="layui-input inline-block" placeholder="结束时间">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label"></label>
                        <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="table-query">
                            <i class="layui-icon layui-icon-search"></i>查询
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md" lay-submit lay-filter="table-reset">
                            <i class="layui-icon layui-icon-refresh"></i>重置
                        </button>
                    </div>
                    <div class="toggle-btn">
                        <a class="layui-hide">展开<i class="layui-icon layui-icon-down"></i></a>
                        <a class="layui-hide">收起<i class="layui-icon layui-icon-up"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>

        <!-- 表格顶部工具栏 -->
        <script type="text/html" id="table-toolbar">
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create" permission="admin.user.create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit" permission="admin.user.edit"><i
                    class="layui-icon layui-icon-edit"></i></button>
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove" permission="admin.user.delete"><i
                    class="layui-icon layui-icon-delete"></i></button>
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'form', 'jquery', 'popup', 'laydate', 'common', 'util'], function () {
            let table = layui.table;
            let form = layui.form;
            let $ = layui.jquery;
            let util = layui.util;
            let popup = layui.popup;
            let laydate = layui.laydate;

            // 字段 创建时间 created_at
            laydate.render({
                elem: "#created_at",
                range: ["#created_at-date-start", "#created_at-date-end"],
            });
            // 表头参数
            let cols = [
                {title: "ID", field: "id", width: 100, sort: true,},
                {title: "用户名", field: "username",},
                {title: "昵称", field: "name",},
                {
                    title: "头像", field: "avatar",
                    templet: function (d) {
                        return '<img src="' + encodeURI(d['avatar']) + '" style="max-width:32px;max-height:32px;" alt="" />'
                    }, width: 90,
                },
                {title: "邮箱", field: "email",},
                {title: "手机", field: "phone",},
                {title: "登录时间", field: "last_login_at",},
                {title: "最后活动", field: "last_active_at",},
                {title: "金币", field: "available_coins"},
                {title: "积分", field: "available_points", hide: true,},
                {title: "创建时间", field: "created_at", hide: true,},
                {title: "更新时间", field: "updated_at", hide: true,},
                {
                    title: "状态",
                    field: "status",
                    templet: function (d) {
                        let field = "status";
                        form.on("switch(" + field + ")", function (data) {
                            let load = layer.load();
                            $.ajax({
                                url: "{{route('admin.users.status')}}",
                                data: {
                                    id: data.elem.value,
                                    status: data.elem.checked ? 0 : 1,
                                },
                                dataType: "json",
                                type: "post",
                                success: function (res) {
                                    layer.close(load);
                                    return popup.success(res.message, function () {
                                        table.reloadData('data-table');
                                    });
                                },
                                error: function (xhr, status, error) {
                                    layer.close(load);
                                    return popup.failure(xhr.responseJSON.message, function () {
                                        data.elem.checked = !data.elem.checked;
                                        form.render();
                                    });
                                }
                            })
                        });
                        let checked = d[field] === 0 ? "checked" : "";
                        return '<input type="checkbox" title="可用|禁用" value="' + util.escape(d['id']) + '" lay-filter="' + util.escape(field) + '" lay-skin="switch" lay-text="' + util.escape('') + '" ' + checked + '/>';
                    },
                    width: 90,
                },
                {
                    title: "操作",
                    toolbar: "#table-bar",
                    align: "center",
                    fixed: "right",
                    width: 150,
                }
            ];

            table.render({
                elem: '#data-table',
                url: "{{route('admin.users.index')}}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            // 表格排序事件
            table.on("sort(data-table)", function (obj) {
                table.reload("data-table", {
                    initSort: obj,
                    scrollPos: "fixed",
                    where: {
                        field: obj.field,
                        order: obj.type
                    }
                });
            });

            // 表格顶部工具栏事件
            table.on("toolbar(data-table)", function (obj) {
                if (obj.event === "create") {
                    layer.open({
                        type: 2,
                        title: "新增用户",
                        shade: 0.1,
                        area: ["550px", "450px"],
                        content: "{{route('admin.users.create')}}",
                        end: function (index) {
                            table.reloadData('data-table');
                        }
                    });
                }
            });

            // 编辑或删除行事件
            table.on("tool(data-table)", function (obj) {
                if (obj.event === "remove") {
                    layer.confirm('确定要删除该用户吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                layer.close(loading);
                                layer.msg(res.message, {icon: 1, time: 1000}, function () {
                                    obj.del();
                                });
                            },
                            error: function (xhr, status, error) {
                                layer.close(loading);
                                layui.popup.failure(xhr.responseJSON.message);
                            }
                        })
                    });
                } else if (obj.event === "edit") {
                    layer.open({
                        type: 2,
                        title: '修改用户',
                        shade: 0.1,
                        area: ["850px", "850px"],
                        content: obj.data.edit_url,
                        end: function (index) {
                            table.reloadData('data-table');
                        }
                    });
                }
            });

            // 表格顶部搜索事件
            form.on("submit(table-query)", function (data) {
                table.reloadData("data-table", {page: {page: 1}, where: data.field})
                return false;
            });

            // 表格顶部搜索重置事件
            form.on("submit(table-reset)", function (data) {
                table.reloadData("data-table", {where: []})
            });
        });
    </script>
@endpush
