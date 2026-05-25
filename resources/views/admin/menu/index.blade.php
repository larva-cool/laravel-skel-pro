@extends('admin.layout')

@section('title', '菜单管理')

@section('content')
    <div class="pear-container">
        <!-- 数据表格 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>
        <!-- 表格顶部工具栏 -->
        <script type="text/html" id="table-toolbar">
            @can('menus.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
            <button class="pear-btn pear-btn-success pear-btn-md" lay-event="expandAll">
                <i class="layui-icon layui-icon-spread-left"></i>
                展开
            </button>
            <button class="pear-btn pear-btn-success pear-btn-md" lay-event="foldAll">
                <i class="layui-icon layui-icon-shrink-right"></i>
                折叠
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('menus.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i
                    class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('menus.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i
                    class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['treeTable', 'jquery', 'common', 'util', 'tablePlus'], function() {
            let treeTable = layui.treeTable;
            let $ = layui.jquery;
            let util = layui.util;
            let common = layui.common;
            let tablePlus = layui.tablePlus;
            let cols = [{
                    title: "标题",
                    field: "name",
                },
                {
                    title: "主键",
                    field: "id",
                    hide: true,
                },
                {
                    title: "权限标识",
                    field: "permission_name"
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
                    title: "url",
                    field: "href",
                },
                {
                    title: "类型",
                    field: "type",
                    width: 80,
                    templet: function(d) {
                        let field = "type";
                        let value = d[field];
                        let css = {
                            "目录": "layui-bg-blue",
                            "菜单": "layui-bg-green",
                        } [value];
                        return '<span class="layui-badge ' + css + '">' + util.escape(value) + '</span>';
                    }
                },
                {
                    title: "排序",
                    field: "order",
                    width: 80,
                },
                {
                    title: "操作",
                    toolbar: "#table-bar",
                    align: "center",
                    fixed: "right",
                    width: 150,
                }
            ];
            // 渲染
            let tableIns = tablePlus.renderTree({
                elem: '#data-table',
                url: '{{ route('admin.menus.index') }}', // 此处为静态模拟数据，实际使用时需换成真实接口
                cols: [cols],
                toolbar: "#table-toolbar",
            }, true);
            // 添加 批量删除 刷新事件
            treeTable.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "add") {
                    tablePlus.createRow("{{ route('admin.menus.create') }}", obj, "新增菜单", ["520px",
                        "520px"
                    ]);
                } else if (obj.event === 'expandAll') {
                    treeTable.expandAll('data-table', true);
                } else if (obj.event === 'foldAll') {
                    treeTable.expandAll('data-table', false);
                }
            });
            // 删除或编辑行事件
            treeTable.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "remove") {
                    tablePlus.removeRow(obj.data.delete_url, obj, "确定要删除该菜单吗？");
                } else if (obj.event === "edit") {
                    tablePlus.editRow(obj.data.edit_url, obj, "修改菜单", ["520px", "520px"]);
                }
            });
        });
    </script>
@endpush
