@extends('admin.layout')

@section('title', '用户组管理')

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
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="create" permission="admin.user_groups.create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            @{{# if(d.show_toolbar){ }}
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit" permission="admin.user_groups.edit"><i class="layui-icon layui-icon-edit"></i></button>
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove" permission="admin.user_groups.delete"><i class="layui-icon layui-icon-delete"></i></button>
            @{{# } }}
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'jquery', 'common'], function () {
            let table = layui.table;
            let $ = layui.jquery;
            let common = layui.common;

            let cols = [
                {title: 'ID', field: 'id', align: 'center', width: 100},
                {title: '用户组名称', field: 'name', align: 'center'},
                {title: '用户组描述', field: 'desc', align: 'center'},
                {title: "创建时间", field: "created_at",},
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
                url: "{{route('admin.user_groups.index')}}",
                cols: [cols],
                toolbar: "#table-toolbar",
            });

            table.on('tool(data-table)', function (obj) {
                if (obj.event === 'remove') {
                    layer.confirm('确定要删除该用户组吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                layer.msg(res.message, {icon: 1, time: 1000}, function () {
                                    obj.del();
                                });
                            },
                            error: function (xhr, status, error) {
                                layui.popup.failure(xhr.responseJSON.message);
                            },
                            complete: function() {
                                layer.close(loading);
                            }
                        })
                    });
                } else if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title: '修改用户组',
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "450px", common.isMobile() ? "100%" : "350px"],
                        content: obj.data.edit_url,
                        end: function (index) {
                            table.reload('data-table');
                        }
                    });
                }
            });

            table.on('toolbar(data-table)', function (obj) {
                if (obj.event === 'create') {
                    layer.open({
                        type: 2,
                        title: '新增用户组',
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "450px", common.isMobile() ? "100%" : "350px"],
                        content: "{{route('admin.user_groups.create')}}",
                        end: function (index) {
                            table.reload('data-table');
                        }
                    });
                }
            });
        });
    </script>
@endpush
