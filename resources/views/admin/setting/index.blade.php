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
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create" permission="admin.settings.create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit" permission="admin.settings.edit"><i class="layui-icon layui-icon-edit"></i></button>
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove" permission="admin.settings.delete"><i class="layui-icon layui-icon-delete"></i></button>
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'form', 'popup', 'common', 'util'], function () {
            let table = layui.table;
            let $ = layui.jquery;
            let common = layui.common;

            let cols = [
                {title: 'ID', field: 'id', align: 'center', width: 100},
                {title: '名称', field: 'name', align: 'center'},
                {title: '配置项', field: 'key', align: 'left'},
                {title: '配置值', field: 'value', align: 'left', width: 200},
                {title: '值类型', field: 'cast_type', align: 'left'},
                {title: '排序', field: 'order', align: 'left'},
                {title: '备注', field: 'remark', align: 'left'},
                {title: "更新时间", field: "updated_at",},
                {
                    title: "操作",
                    toolbar: "#table-bar",
                    align: "center",
                    fixed: "right",
                    width: 195,
                },
            ];

            table.render({
                elem: '#data-table',
                url: "{{route('admin.settings.index')}}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            table.on('tool(data-table)', function (obj) {
                if (obj.event === 'remove') {
                    layer.confirm('确定要删除该配置项吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                layer.close(loading);
                                layer.msg('删除成功！', {icon: 1, time: 1000}, function () {
                                    obj.del();
                                });
                            },
                            error: function (xhr, status, error) {
                                layer.close(loading);
                                layui.popup.failure(xhr.responseJSON.message);
                            }
                        })
                    });
                } else if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title: '修改配置项',
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "750px", common.isMobile() ? "100%" : "550px"],
                        content: obj.data.edit_url,
                        end: function (index) {
                            table.reloadData('data-table');
                        }
                    });
                }
            });

            table.on('toolbar(data-table)', function (obj) {
                if (obj.event === 'create') {
                    layer.open({
                        type: 2,
                        title: '新增配置项',
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "750px", common.isMobile() ? "100%" : "550px"],
                        content: "{{route('admin.settings.create')}}",
                        end: function (index) {
                            table.reloadData('data-table');
                        }
                    });
                }
            });
        });
    </script>
@endpush
