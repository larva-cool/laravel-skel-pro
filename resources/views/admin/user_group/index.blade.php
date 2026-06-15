@extends('admin.layout')

@section('title', '用户组管理')

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
            @can('user_groups.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('user_groups.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('user_groups.delete')<button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'common', 'tablePlus'], function() {
            let table = layui.table;
            let tablePlus = layui.tablePlus;

            let cols = [
                {
                    title: 'ID',
                    field: 'id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '用户组名称',
                    field: 'name',
                    align: 'center'
                },
                {
                    title: '用户组描述',
                    field: 'desc',
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
                url: "{{ route('admin.user_groups.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj, '确定要删除该用户组吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改用户组', ["450px", "350px"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.user_groups.create') }}", obj, "新增用户组", ["450px",
                        "350px"
                    ]);
                }
            });
        });
    </script>
@endpush
