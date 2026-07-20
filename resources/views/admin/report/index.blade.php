@extends('admin.layout')

@section('title', '举报管理')

@section('content')
    <div class="pear-container">
        <div class="layui-card">
            <div class="layui-card-body">
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>

        <script type="text/html" id="table-bar">
            @can('reports.edit')<button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button>@endcan
            @can('reports.delete')<button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>@endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'form', 'popup', 'common', 'util', 'tablePlus'], function() {
            let table = layui.table;
            let tablePlus = layui.tablePlus;
            let util = layui.util;

            let cols = [{
                    title: 'ID',
                    field: 'id',
                    align: 'center',
                    width: 80
                },
                {
                    title: '举报人',
                    field: 'user_name',
                    align: 'center',
                    width: 120
                },
                {
                    title: '对象类型',
                    field: 'reportable_type',
                    align: 'center',
                    width: 120
                },
                {
                    title: '对象ID',
                    field: 'reportable_id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '原因',
                    field: 'reason',
                    align: 'center',
                    width: 120,
                    templet: function(d) {
                        return d.reason ? util.escape(d.reason.label) : '';
                    }
                },
                {
                    title: '补充说明',
                    field: 'content',
                    align: 'left',
                    templet: function(d) {
                        let str = d.content || '';
                        if (str.length > 60) {
                            str = str.substring(0, 60) + '...';
                        }
                        return util.escape(str);
                    }
                },
                {
                    title: '状态',
                    field: 'status',
                    align: 'center',
                    width: 100,
                    templet: function(d) {
                        return d.status ? util.escape(d.status.label) : '';
                    }
                },
                {
                    title: '举报时间',
                    field: 'created_at',
                    width: 170
                },
                {
                    title: '操作',
                    toolbar: '#table-bar',
                    align: 'center',
                    fixed: 'right',
                    width: 160,
                },
            ];

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.reports.index') }}",
                cols: [cols]
            });

            table.on('tool(' + tableIns.config.id + ')', function(obj) {
                if (obj.event === 'remove') {
                    tablePlus.confirmDelete(obj.data.delete_url, obj, '确定要删除该举报吗？');
                } else if (obj.event === 'edit') {
                    tablePlus.editRow(obj.data.edit_url, obj, '处理举报', ["85%", "85%"]);
                }
            });
        });
    </script>
@endpush
