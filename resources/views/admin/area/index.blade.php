@extends('admin.layout')

@section('title', '地区管理')

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
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add" permission="admin.areas.create">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            <button class="pear-btn pear-btn-success pear-btn-md" lay-event="expandAll">
                <i class="layui-icon layui-icon-spread-left"></i>
                展开
            </button>
            <button class="pear-btn pear-btn-success pear-btn-md" lay-event="foldAll">
                <i class="layui-icon layui-icon-shrink-right"></i>
                折叠
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit" permission="admin.areas.edit"><i
                    class="layui-icon layui-icon-edit"></i></button>
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove" permission="admin.areas.delete"><i
                    class="layui-icon layui-icon-delete"></i></button>
        </script>
    </div>
@endsection
@push('scripts')
    <script>
        layui.use(['treeTable', 'jquery', 'common', 'util'], function () {
            let treeTable = layui.treeTable;
            let $ = layui.jquery;
            let common = layui.common;
            let cols = [
                {title: "地区名称", field: "name",},
                {title: "主键", field: "id", hide: true,},
                {title: "城市区号", field: "city_code",},
                {title: "地区编码", field: "area_code",},
                {title: "纬度", field: "lat",},
                {title: "经度", field: "lng",},
                {title: "创建时间", field: "created_at", hide: true,},
                {title: "更新时间", field: "updated_at", hide: true,},
                {title: "排序", field: "order", width: 80,},
                {title: "操作", toolbar: "#table-bar", align: "center", fixed: "right", width: 150,}
            ];
            // 渲染
            treeTable.render({
                elem: '#data-table',
                url: '{{route('admin.areas.index')}}', // 此处为静态模拟数据，实际使用时需换成真实接口
                tree: {
                    customName: {
                        isParent: 'is_parent',
                        name: 'name',
                        pid: 'parent_id',
                    },
                    // 异步加载子节点
                    async: {
                        enable: true,
                        autoParam: ["parent_id=id"]
                    },
                    view: {
                        expandAllDefault: false,
                    }
                },
                cols: [cols],
                page: true,
                limit: 50,
                limits: [50,100,150,200],
                toolbar: "#table-toolbar",
                defaultToolbar: [{
                    title: "刷新",
                    layEvent: "refresh",
                    icon: "layui-icon-refresh",
                }, "filter", "print", "exports"],
                loading: true, // 显示加载状态
                text: {
                    none: '暂无数据' // 无数据时的提示文本
                },
                request: {
                    pageName: 'page', // 页码参数名
                    limitName: 'per_page', // 每页数据条数参数名
                },
                dataType: 'json',
                headers: {
                    Accept: 'application/json'
                },
                parseData: function (res) { // 自定义数据解析
                    return {
                        "code": 0, // 解析接口状态
                        "msg": 'ok', // 解析提示文本
                        "count": res.meta.total, // 解析数据长度
                        "data": res.data // 解析数据列表
                    };
                }
            });
            // 添加 批量删除 刷新事件
            treeTable.on("toolbar(data-table)", function (obj) {
                if (obj.event === "add") {
                    layer.open({
                        type: 2,
                        title: "新增地区",
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "520px", common.isMobile() ? "100%" : "520px"],
                        content: "{{route('admin.areas.create')}}",
                        end: function () {
                            treeTable.reload('data-table');
                        }
                    });
                } else if(obj.event === 'expandAll') {
                    treeTable.expandAll('data-table', true);
                } else if(obj.event === 'foldAll') {
                    treeTable.expandAll('data-table', false);
                } else if (obj.event === "refresh") {
                    treeTable.reload('data-table');
                }
            });
            // 删除或编辑行事件
            treeTable.on("tool(data-table)", function (obj) {
                if (obj.event === "remove") {
                    layer.confirm('确定要删除该地区吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                layer.close(loading);
                                layer.msg(res.message, {icon: 1, time: 1000}, function () {
                                    obj.del();
                                });
                            },
                            error: function (xhr, status, error) {
                                layer.close(loading);
                                layui.popup.failure(xhr.responseJSON.message);
                            }
                        })
                    });
                } else if (obj.event === "edit") {
                    layer.open({
                        type: 2,
                        title: "修改地区",
                        shade: 0.1,
                        area: [common.isMobile() ? "100%" : "520px", common.isMobile() ? "100%" : "520px"],
                        content: obj.data.edit_url,
                        end: function () {
                            treeTable.reload('data-table');
                        }
                    });
                }
            });
        });
    </script>
@endpush
