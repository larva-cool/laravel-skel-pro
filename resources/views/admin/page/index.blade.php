@extends('admin.layout')

@section('title', '页面管理')

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
        @can('pages.create')
        <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
            <i class="layui-icon layui-icon-add-1"></i>新增
        </button>
        @endcan
    </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
        @can('pages.edit')<button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>@endcan
        @can('pages.delete')<button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>@endcan
    </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'form', 'popup', 'common', 'util', 'tablePlus'], function() {
            let table = layui.table;
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
                    title: '描述',
                    field: 'desc',
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
                url: "{{ route('admin.pages.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            table.on('tool(data-table)', function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.confirmDelete(obj.data.delete_url, obj, '确定要删除该页面吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改页面', ["85%", "85%"]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.pages.create') }}", obj, "新增页面", ["85%", "85%"]);
                }
            });
        });
    </script>
@endpush
