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
                            <div class="layui-input-block" id="last_login_at">
                                <input type="text" autocomplete="off" name="last_login_at[]" id="last_login_at-date-start"
                                    class="layui-input inline-block" placeholder="开始时间">
                                -
                                <input type="text" autocomplete="off" name="last_login_at[]" id="last_login_at-date-end"
                                    class="layui-input inline-block" placeholder="结束时间">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">注册时间</label>
                        <div class="layui-input-block">
                            <div class="layui-input-block" id="created_at">
                                <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-start"
                                    class="layui-input inline-block" placeholder="开始时间">
                                -
                                <input type="text" autocomplete="off" name="created_at[]" id="created_at-date-end"
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
            @can('users.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('users.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i
                    class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('users.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i
                    class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'laydate', 'tablePlus'], function() {
            let table = layui.table;
            let laydate = layui.laydate;
            let tablePlus = layui.tablePlus;

            // 字段 最后登录时间 last_login_at
            laydate.render({
                elem: "#last_login_at",
                range: ["#last_login_at-date-start", "#last_login_at-date-end"],
            });
            // 字段 创建时间 created_at
            laydate.render({
                elem: "#created_at",
                range: ["#created_at-date-start", "#created_at-date-end"],
            });
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
                    hide: true,
                },
                {
                    title: "昵称",
                    field: "name",
                },
                {
                    title: "头像",
                    field: "avatar",
                    templet: function(d) {
                        return '<img src="' + encodeURI(d['avatar']) +
                            '" style="max-width:32px;max-height:32px;" alt="" />'
                    },
                    width: 90,
                },
                {
                    title: "邮箱",
                    field: "email",
                    hide: true,
                },
                {
                    title: "手机",
                    field: "phone",
                },
                {
                    title: "最后登录",
                    field: "last_login_at",
                },
                {
                    title: "最后活动",
                    field: "last_active_at",
                },
                {
                    title: "金币",
                    field: "available_coins",
                    sort: true,
                },
                {
                    title: "积分",
                    field: "available_points",
                    sort: true,
                    hide: true,
                },
                {
                    title: "登录次数",
                    field: "login_count",
                    hide: true,
                },
                {
                    title: "最后登录IP",
                    field: "last_login_ip",
                    hide: true,
                },
                {
                    title: "注册时间",
                    field: "created_at",
                    sort: true,
                    hide: true,
                },
                {
                    title: "更新时间",
                    field: "updated_at",
                    hide: true,
                },
                {
                    title: "状态",
                    field: "status",
                    templet: function(d) {
                        return tablePlus.statusSwitch("{{ route('admin.users.status') }}", d, "status");
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

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.users.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            // 表格顶部工具栏事件
            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "create") {
                    tablePlus.createRow("{{ route('admin.users.create') }}", obj, "新增用户", ["550px",
                        "450px"
                    ]);
                }
            });

            // 编辑或删除行事件
            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "remove") {
                    tablePlus.deleteRow(obj.data.delete_url, obj, '确定要删除该用户吗？');
                } else if (obj.event === "edit") {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改用户', ["850px", "850px"]);
                }
            });
        });
    </script>
@endpush
