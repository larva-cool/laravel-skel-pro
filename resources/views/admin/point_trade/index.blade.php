@extends('admin.layout')

@section('title', '金币记录')

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

        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">

        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'tablePlus'], function() {
            let tablePlus = layui.tablePlus;

            let cols = [
                {
                    title: 'ID',
                    field: 'id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '用户',
                    field: 'user_name',
                    align: 'left'
                },
                {
                    title: "头像",
                    field: "user_avatar",
                    templet: function(d) {
                        return '<img src="' + encodeURI(d['user_avatar']) +
                            '" style="max-width:32px;max-height:32px;" alt="" />'
                    },
                    width: 90,
                },
                {
                    title: '交易类型',
                    field: 'type_label',
                    align: 'left',
                    width: 200
                },
                {
                    title: '交易积分',
                    field: 'points',
                    align: 'left'
                },
                {
                    title: '描述',
                    field: 'description',
                    align: 'left'
                },
                {
                    title: "交易时间",
                    field: "created_at",
                },
            ];

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.points.index') }}",
                cols: [cols],
            });
        });
    </script>
@endpush
