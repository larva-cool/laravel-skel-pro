@extends('admin.layout')

@section('title', '短信验证码')

@section('content')
    <div class="pear-container">
        <!-- 搜索栏 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form" action="" lay-filter="search-form">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">手机号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="phone" placeholder="请输入手机号" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">场景</label>
                            <div class="layui-input-inline">
                                <input type="text" name="scene" placeholder="请输入场景" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="state">
                                    <option value="">全部</option>
                                    <option value="0">未使用</option>
                                    <option value="1">已使用</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="search-submit">
                                <i class="layui-icon layui-icon-search"></i> 搜索
                            </button>
                            <button type="reset" class="pear-btn pear-btn-md">
                                <i class="layui-icon layui-icon-refresh"></i> 重置
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
        layui.use(['table', 'tablePlus', 'form'], function() {
            let tablePlus = layui.tablePlus;
            let form = layui.form;
            let table = layui.table;

            let cols = [{
                    title: 'ID',
                    field: 'id',
                    align: 'center',
                    width: 100
                },
                {
                    title: '手机号',
                    field: 'phone',
                    align: 'left',
                    width: 140
                },
                {
                    title: '场景',
                    field: 'scene',
                    align: 'left',
                    width: 120
                },
                {
                    title: '验证码',
                    field: 'code',
                    align: 'center',
                    width: 120
                },
                {
                    title: 'IP',
                    field: 'ip',
                    align: 'left',
                    width: 140
                },
                {
                    title: '状态',
                    field: 'state_label',
                    align: 'center',
                    width: 100,
                    templet: function(d) {
                        if (d.state === 1) {
                            return '<span class="layui-badge layui-bg-green">' + d.state_label + '</span>';
                        }
                        return '<span class="layui-badge layui-bg-gray">' + d.state_label + '</span>';
                    }
                },
                {
                    title: '验证次数',
                    field: 'verify_count',
                    align: 'center',
                    width: 100
                },
                {
                    title: '发送时间',
                    field: 'send_at',
                    align: 'center',
                    width: 190
                },
                {
                    title: '使用时间',
                    field: 'usage_at',
                    align: 'center',
                    width: 190
                },
            ];

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.phone_codes.index') }}",
                cols: [cols],
            });

            form.on('submit(search-submit)', function(data) {
                table.reload('data-table', {
                    where: data.field,
                    page: {
                        curr: 1
                    }
                });
                return false;
            });
        });
    </script>
@endpush
