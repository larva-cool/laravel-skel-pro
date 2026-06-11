@extends('admin.layout')

@section('title', '附件管理')

@section('content')
    <div class="pear-container">
        <!-- 顶部查询表单 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form top-search-from">

                    <div class="layui-form-item">
                        <label class="layui-form-label">关键词</label>
                        <div class="layui-input-block">
                            <input type="text" name="keyword" value="" class="layui-input" placeholder="请输入搜索关键词">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">扩展名</label>
                        <div class="layui-input-block">
                            <input type="text" name="file_ext" value="" class="layui-input" placeholder="请输入文件扩展名">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">上传时间</label>
                        <div class="layui-input-block">
                            <div class="layui-input-block" id="created_at">
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-start"
                                    class="layui-input inline-block" placeholder="开始时间">
                                -
                                <input type="text" autocomplete="off" name="last_login_at[]" id="created_at-date-end"
                                    class="layui-input inline-block" placeholder="结束时间">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label"></label>
                        <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="table-query">
                            <i class="layui-icon layui-icon-search"></i>查询
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md" lay-submit lay-filter="table-reset">
                            <i class="layui-icon layui-icon-refresh"></i>重置
                        </button>
                    </div>
                    <div class="toggle-btn">
                        <a class="layui-hide">展开<i class="layui-icon layui-icon-down"></i></a>
                        <a class="layui-hide">收起<i class="layui-icon layui-icon-up"></i></a>
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
            @can('attachments.create')
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create">
                <i class="layui-icon layui-icon-add-1"></i>上传
            </button>
            @endcan
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @can('attachments.delete')
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button>
            @endcan
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        // 字段 创建时间 created_at
        layui.use(["laydate"], function() {
            layui.laydate.render({
                elem: "#created_at",
                range: ["#created_at-date-start", "#created_at-date-end"],
            });
        });
        layui.use(['table', 'form', 'jquery', 'popup', 'common', 'util', 'tablePlus'], function() {
            let table = layui.table;
            let tablePlus = layui.tablePlus;
            // 表头参数
            let cols = [
                {
                    title: "ID",
                    field: "id",
                    width: 100,
                    sort: true,
                },
                {
                    title: "上传用户ID",
                    field: "user_id",
                    hide: true,
                },
                {
                    title: "上传用户",
                    field: "user_name",
                },
                {
                    title: "存储",
                    field: "storage",
                },
                {
                    title: "原始文件名",
                    field: "origin_name",
                },
                {
                    title: "文件名",
                    field: "file_name",
                },
                {
                    title: "文件Url",
                    field: "file_url",
                    templet: function(d) {
                        return tablePlus.image(d['file_url'], 32, 32);
                    },
                    width: 90,
                },
                {
                    title: "文件大小",
                    field: "file_size",
                },
                {
                    title: "文件类型",
                    field: "mime_type",
                },
                {
                    title: "上传时间",
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
                    width: 150,
                }
            ];

            let tableIns = tablePlus.render({
                elem: '#data-table',
                url: "{{ route('admin.attachments.index') }}",
                cols: [cols],
                toolbar: "#table-toolbar"
            });

            // 表格顶部工具栏事件
            table.on("toolbar(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "create") {
                    tablePlus.createRow("{{ route('admin.attachments.create') }}", obj, '上传文件', ["500px",
                        "550px"
                    ]);
                }
            });

            // 编辑或删除行事件
            table.on("tool(" + tableIns.config.id + ")", function(obj) {
                if (obj.event === "remove") {
                    tablePlus.deleteRow(obj.data.delete_url, obj);
                }
            });
        });
    </script>
@endpush
