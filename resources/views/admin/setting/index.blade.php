@extends('admin.layout')

@section('title', '设置项管理')

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
            @can('settings.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('settigns.edit')
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>
            @endcan
            @can('settings.delete')
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
            let common = layui.common;
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
                    align: 'center'
                },
                {
                    title: '配置项',
                    field: 'key',
                    align: 'left'
                },
                {
                    title: '配置值',
                    field: 'value',
                    align: 'left',
                    width: 200
                },
                {
                    title: '值类型',
                    field: 'cast_type',
                    align: 'left'
                },
                {
                    title: '排序',
                    field: 'order',
                    align: 'left'
                },
                {
                    title: '备注',
                    field: 'remark',
                    align: 'left'
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
                url: "{{ route('admin.settings.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj, '确定要删除该配置项吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改配置项', ["750px", "550px"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.settings.create') }}", obj, "新增配置项", ["750px",
                        "550px"
                    ]);
                }
            });
        });
    </script>
@endpush
