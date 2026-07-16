@extends('admin.layout')

@section('title', '任务管理')

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
        @can('tasks.create')
        <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
            <i class="layui-icon layui-icon-add-1"></i>新增
        </button>
        @endif
        <button class="pear-btn pear-btn-warming pear-btn-xs" lay-event="repair_log">
            <i class="layui-icon layui-icon-set-fill"></i>修复完成数
        </button>
    </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
        @can('tasks.edit')
        <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
        @endif
        @can('tasks.delete')
        <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
        @endif
    </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'tablePlus'], function() {
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
                    title: '任务类型',
                    field: 'type_name',
                    align: 'left'
                },
                {
                    title: '奖励积分',
                    field: 'coins',
                    align: 'left'
                },
                {
                    title: "活跃度奖励",
                    field: "activity_bonus",
                    templet: function(d) {
                        return tablePlus.status(d, "activity_bonus");
                    },
                    width: 120,
                },
                {
                    title: '完成数',
                    field: 'log_count',
                    align: 'left'
                },
                {
                    title: '排序',
                    field: 'order',
                    align: 'left'
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
                url: "{{ $tasks_url }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj, '确定要删除该任务吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改任务', ["550px", "80%"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ $create_url }}", obj, "新增任务", ["550px", "80%"]);
                } else if (obj.event === 'repair_log') {
                    tablePlus.confirmPost("{{ $repair_url }}", {}, obj, "确认修复任务完成数码？");
                }
            });
        });
    </script>
@endpush
