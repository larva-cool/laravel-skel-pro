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
            @can('task_groups.edit')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('task_groups.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('task_groups.edit')
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
            let tablePlus = layui.tablePlus;

            let cols = [{
                    title: 'ID',
                    field: 'id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '名称',
                    field: 'name',
                    align: 'left'
                },
                {
                    title: '描述',
                    field: 'description',
                    align: 'left'
                },
                {
                    title: '类型',
                    field: 'type_name',
                    align: 'left',
                    maxWidth: 100
                },
                {
                    title: '任务数',
                    field: 'tasks_count',
                    align: 'left',
                    event: 'tasks',
                    templet: function(d) {
                        return '<i class="layui-icon layui-icon-set"></i>  ' + d.tasks_count;
                    },
                    maxWidth: 100
                },
                {
                    title: '完成数',
                    field: 'completed_count',
                    align: 'left',
                    event: 'show',
                    maxWidth: 100
                },
                {
                    title: '是否可见',
                    field: 'visibility',
                    align: 'center',
                    templet: function(d) {
                        return d.visibility === 1 ? '是' : '否';
                    },
                    maxWidth: 100
                },
                {
                    title: "状态",
                    field: "status",
                    templet: function(d) {
                        return tablePlus.statusSwitch(d.status_url, d, "status");
                    },
                    width: 90,
                },

                {
                    title: '排序',
                    field: 'order',
                    align: 'center',
                    maxWidth: 100
                },
                {
                    title: "创建时间",
                    field: "created_at",
                },
                {
                    title: "更新时间",
                    field: "updated_at",
                    hide: true,
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
                url: "{{ route('admin.task_groups.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj, '确定要删除该任务组吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改任务组', ["550px", "550px"]);
                } else if (obj.event === 'show') {
                    tablePlus.showRow(obj.data.show_url, obj, '任务组详情', ["80%", "80%"]);
                } else if (obj.event === 'tasks') {
                    tablePlus.showRow(obj.data.tasks_url, obj, '任务列表', ["80%", "80%"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.task_groups.create') }}", obj, "新增任务组", ["550px",
                        "550px"
                    ]);
                }
            });
        });
    </script>
@endpush
