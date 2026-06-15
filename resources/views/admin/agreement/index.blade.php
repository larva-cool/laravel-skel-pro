@extends('admin.layout')

@section('title', '协议管理')

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
            @can('agreements.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('agreements.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('agreements.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'form', 'popup', 'common', 'util', 'tablePlus'], function() {
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
                    title: '标题',
                    field: 'title',
                    align: 'center'
                },
                {
                    title: '类型',
                    field: 'type',
                    align: 'left'
                },
                {
                    title: '状态',
                    field: 'status',
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
                url: "{{ route('admin.agreements.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj);
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改协议', ["85%", "85%"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.agreements.create') }}", obj, '新增协议', ["85%",
                        "85%"
                    ]);
                }
            });
        });
    </script>
@endpush
