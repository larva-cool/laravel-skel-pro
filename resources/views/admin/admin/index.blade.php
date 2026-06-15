@extends('admin.layout')

@section('title', '管理员管理')

@section('content')
    <div class="pear-container">
        <!-- 顶部查询表单 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form top-search-from">

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
            @can('admins.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('admins.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('admins.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'laydate', 'tablePlus'], function() {
            let table = layui.table;
            let util = layui.util;
            let tablePlus = layui.tablePlus;
            // 表头参数
            let cols = [{
                    title: "ID",
                    field: "id",
                    width: 100,
                    sort: true,
                },
                {
                    title: "用户名",
                    field: "username",
                },
                {
                    title: "昵称",
                    field: "name",
                },
                {
                    title: "头像",
                    field: "avatar",
                    templet: function(d) {
                        return tablePlus.image(d['avatar'], 32, 32);
                    },
                    width: 90,
                },
                {
                    title: "邮箱",
                    field: "email",
                },
                {
                    title: "手机",
                    field: "phone",
                },
                {
                    title: "创建时间",
                    field: "created_at",
                    hide: true,
                },
                {
                    title: "更新时间",
                    field: "updated_at",
                    hide: true,
                },
                {
                    title: "登录时间",
                    field: "last_login_at",
                },
                {
                    title: "登录次数",
                    field: "login_count",
                },
                {
                    title: "角色",
                    field: "roles",
                    templet: function(d) {
                        let field = "roles";
                        if (typeof d[field] == "undefined") return "";
                        let items = [];
                        layui.each(d[field], function(k, v) {
                            items.push(v.name);
                        });
                        return util.escape(items.join(","));
                    }
                },
                {
                    title: "状态",
                    field: "status",
                    templet: function(d) {
                        return tablePlus.statusSwitch("{{ route('admin.admins.status') }}", d, "status");
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

            layui.laydate.render({
                elem: "#created_at",
                range: ["#created_at-date-start", "#created_at-date-end"],
            });

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.admins.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            // 表格顶部工具栏事件
            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "create") {
                    tablePlus.createRow("{{ route('admin.admins.create') }}", obj, "新增管理员", ["520px",
                        "520px"
                    ]);
                }
            });

            // 编辑或删除行事件
            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "remove") {
                    tablePlus.deleteRow(obj.data.delete_url, obj);
                } else if (obj.event === "edit") {
                    tablePlus.editRow(obj.data.edit_url, obj, "修改管理员", ["520px", "520px"]);
                }
            });
        });
    </script>
@endpush
