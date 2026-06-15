@extends('admin.layout')

@section('title', '公告管理')

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
            @can('announcements.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('announcements.edit')<button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>@endcan
            @can('announcements.delete')<button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>@endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'form', 'popup', 'common', 'util', 'tablePlus'], function() {
            let table = layui.table;
            let $ = layui.jquery;
            let tablePlus = layui.tablePlus;
            let util = layui.util;

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
                    title: "范围",
                    field: "coverage",
                    templet: function(d) {
                        let field = "coverage";
                        if (typeof d[field] == "undefined") return "";
                        let items = [];
                        layui.each(d[field], function(k, v) {
                            items.push(v);
                        });
                        return util.escape(items.join(","));
                    }
                },
                {
                    title: "状态",
                    field: "status",
                    templet: function(d) {
                        return tablePlus.statusSwitch('{{ route('admin.announcements.status') }}', d,
                            "status");
                    },
                    width: 90,
                },
                {
                    title: '阅读次数',
                    field: 'read_count',
                    align: 'center',
                    width: 100
                },
                {
                    title: "生效时间",
                    field: "effective_time_type",
                    templet: function(d) {
                        if (d.effective_time_type == 0) {
                            return "永久生效";
                        } else if (d.effective_time_type == 1) {
                            return d.effective_start_time + " 至 " + d.effective_end_time;
                        }
                    },
                    width: 400,
                },
                {
                    title: "创建时间",
                    field: "created_at",
                    width: 190
                },
                {
                    title: "更新时间",
                    field: "updated_at",
                    width: 190
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
                url: "{{ route('admin.announcements.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.deleteRow(obj.data.delete_url, obj);
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '修改公告', ["85%",
                        "85%"
                    ]);
                }
            });

            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === 'create') {
                    tablePlus.createRow("{{ route('admin.announcements.create') }}", obj, '新增公告', ["85%",
                        "85%"
                    ]);
                }
            });
        });
    </script>
@endpush
