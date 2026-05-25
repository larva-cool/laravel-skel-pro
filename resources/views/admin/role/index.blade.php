@extends('admin.layout')

@section('title', '角色管理')

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
            @can('roles.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('roles.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('roles.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'common', 'tablePlus'], function() {
            let table = layui.table;
            let $ = layui.jquery;
            let common = layui.common;
            let tablePlus = layui.tablePlus;

            let cols = [{
                    title: '角色ID',
                    field: 'id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '角色名称',
                    field: 'name',
                    align: 'center'
                },
                {
                    title: 'Guard',
                    field: 'guard_name',
                    align: 'center'
                },
                {
                    title: "创建时间",
                    field: "created_at",
                },
                {
                    title: "更新时间",
                    field: "updated_at",
                },
                {
                    title: "操作",
                    toolbar: "#table-bar",
                    align: "center",
                    fixed: "right",
                    width: 195,
                },
            ];

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.roles.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.confirmDelete(obj.data.delete_url, obj, '确定要删除该角色吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改角色', ["650px",
                        "700px"
                    ]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.roles.create') }}", obj, "新增角色", ["650px",
                        "650px"
                    ]);
                }
            });
        });
    </script>
@endpush
