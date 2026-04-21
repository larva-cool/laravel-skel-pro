@extends('admin.layout')

@section('title', '字典管理')

@section('content')
    <div class="pear-container">
        <div class="layui-row layui-col-space10">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-body">
                        <form class="layui-form" action="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">字典名称</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="name" placeholder="" class="layui-input">
                                </div>
                                <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit
                                        lay-filter="dict-type-query">
                                    <i class="layui-icon layui-icon-search"></i>
                                    查询
                                </button>
                                <button type="reset" class="pear-btn pear-btn-md" lay-submit
                                        lay-filter="dict-type-reset">
                                    <i class="layui-icon layui-icon-refresh"></i>
                                    重置
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-body">
                        <table id="dict-type-table" lay-filter="dict-type-table"></table>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-body">
                        <svg class="empty" style="margin-top: 50px;margin-left: 220px;margin-bottom: 80px;" width="184"
                             height="152"
                             viewBox="0 0 184 152" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fillRule="evenodd">
                                <g transform="translate(24 31.67)">
                                    <ellipse fillOpacity=".8" fill="#F5F5F7" cx="67.797" cy="106.89" rx="67.797"
                                             ry="12.668"></ellipse>
                                    <path
                                        d="M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z"
                                        fill="#AEB8C2"></path>
                                    <path
                                        d="M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z"
                                        fill="url(#linearGradient-1)" transform="translate(13.56)"></path>
                                    <path
                                        d="M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z"
                                        fill="#F5F5F7"></path>
                                    <path
                                        d="M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z"
                                        fill="#DCE0E6"></path>
                                </g>
                                <path
                                    d="M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z"
                                    fill="#DCE0E6"></path>
                                <g transform="translate(149.65 15.383)" fill="#FFF">
                                    <ellipse cx="20.654" cy="3.167" rx="2.849" ry="2.815"></ellipse>
                                    <path d="M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z"></path>
                                </g>
                            </g>
                        </svg>
                        <table id="dict-data-table" lay-filter="dict-data-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/html" id="dict-type-toolbar">
        <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
            <i class="layui-icon layui-icon-add-1"></i>
            新增
        </button>
    </script>

    <script type="text/html" id="dict-type-bar">
        <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit">
            <i class="layui-icon layui-icon-edit"></i>
        </button>
        <button class="pear-btn pear-btn-warming pear-btn-xs" lay-event="details">
            <i class="layui-icon layui-icon-transfer"></i>
        </button>
        <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove">
            <i class="layui-icon layui-icon-delete"></i>
        </button>
    </script>

    <script type="text/html" id="dict-data-toolbar">
        <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
            <i class="layui-icon layui-icon-add-1"></i>
            新增
        </button>
        <button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove">
            <i class="layui-icon layui-icon-delete"></i>
            删除
        </button>
    </script>

    <script type="text/html" id="dict-data-bar">
        <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="edit"><i
                class="layui-icon layui-icon-edit"></i>
        </button>
        <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="remove"><i
                class="layui-icon layui-icon-delete"></i>
        </button>
    </script>
@endsection
@push('scripts')
    <script>
        layui.use(['table', 'form', 'jquery', 'util', 'popup','common', 'tablePlus'], function () {
            let table = layui.table;
            let form = layui.form;
            let util = layui.util;
            let $ = layui.jquery;
            let popup = layui.popup;
            let common = layui.common;
            let tablePlus = layui.tablePlus;
            let parentId;
            let cols = [
                {title: '字典名称', field: 'name', align: 'center', width: 120},
                {title: '描述', field: 'description', align: 'center'},
                {title: '字典编码', field: 'code', align: 'center',},
                {title: "字典状态", field: "status", templet: function (d) {return tablePlus.statusSwitch('{{route('admin.dicts.status')}}', d, "status");}, width: 90,},
                {title: '操作', toolbar: '#dict-type-bar', align: 'center', width: 190}
            ];
            let dataCols = [
                [
                    {type: 'checkbox'},
                    {title: '标签', field: 'name', align: 'center', width: 120},
                    {title: '对应值', field: 'code', align: 'center'},
                    {title: "状态", field: "status", templet: function (d) {return tablePlus.statusSwitch('{{route('admin.dicts.status')}}', d, "status");}, width: 90,},
                    {title: '操作', toolbar: '#dict-data-bar', align: 'center', width: 180}
                ]
            ];
            let renderData = function (id) {
                parentId = id;
                $(".empty").hide();
                table.render({
                    elem: '#dict-data-table',
                    url: "{{route('admin.dicts.index')}}",
                    where: {
                        parent_id: id
                    },
                    height: 'full-148',
                    cols: dataCols,
                    toolbar: '#dict-data-toolbar',
                });
            }
            table.render({
                elem: '#dict-type-table',
                url: "{{route('admin.dicts.index')}}",
                cols: [cols],
                height: 'full-148',
                toolbar: '#dict-type-toolbar',
            });
            table.on('tool(dict-type-table)', function (obj) {
                if (obj.event === 'remove') {
                    layer.confirm('确定要删除吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                if(res.code === 0) {
                                    layer.msg(res.message, {icon: 1, time: 1000}, function () {
                                        obj.del();
                                    });
                                } else {
                                    layer.msg(res.message, {icon: 2, time: 1000});
                                }
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
                        title: '修改',
                        shade: 0.1,
                        area: ['500px', '400px'],
                        content: obj.data.edit_url,
                        end: function (index) {
                            table.reload('dict-type-table');
                        }
                    });
                } else if (obj.event === 'details') {
                    renderData(obj.data['id']);
                }
            });

            table.on('toolbar(dict-type-table)', function (obj) {
                if (obj.event === 'add') {
                    layer.open({
                        type: 2,
                        title: '新增',
                        shade: 0.1,
                        area: ['500px', '400px'],
                        content: "{{route('admin.dicts.create')}}",
                        end: function (index) {
                            table.reload('dict-type-table');
                        }
                    });
                }
            });

            form.on('submit(dict-type-query)', function (data) {
                table.reload('dict-type-table', {
                    where: data.field
                })
                return false;
            });
            // 表格顶部搜索重置事件
            form.on("submit(dict-type-reset)", function (data) {
                table.reload("dict-type-table", {where: []})
            });

            table.on('tool(dict-data-table)', function (obj) {
                if (obj.event === 'remove') {
                    layer.confirm('确定要删除吗？', {icon: 3, title: '提示'}, function (index) {
                        let loading = layer.load();
                        $.ajax({
                            url: obj.data.delete_url,
                            dataType: 'json',
                            type: 'delete',
                            success: function (res) {
                                layer.msg('删除成功！', {icon: 1, time: 1000}, function () {
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
                        title: '修改',
                        shade: 0.1,
                        area: ['500px', '400px'],
                        content: obj.data.edit_data_url,
                        end: function (index) {
                            table.reloadData('dict-data-table');
                        }
                    });
                }
            });

            table.on('toolbar(dict-data-table)', function (obj) {
                if (obj.event === 'add') {
                    layer.open({
                        type: 2,
                        title: '新增',
                        shade: 0.1,
                        area: ['500px', '400px'],
                        content: "{{route('admin.dicts.create_data')}}?parent_id=" + parentId,
                        end: function (index) {
                            table.reload('dict-data-table');
                        }
                    });
                } else if (obj.event === 'batchRemove') {
                    let checkIds = common.checkField(obj, "id");
                    if (checkIds === "") {
                        popup.warning("未选中数据");
                        return false;
                    }
                    let data = {};
                    data["ids"] = checkIds.split(",");
                    layer.confirm("确定删除选中?", {
                        icon: 3,
                        title: "提示"
                    }, function(index) {
                        layer.close(index);
                        let loading = layer.load();
                        $.ajax({
                            url: "{{route('admin.dicts.batch_destroy')}}",
                            data: data,
                            dataType: "json",
                            type: "post",
                            success: function(res) {
                                layui.popup.success(res.message, function (){
                                    table.reloadData('dict-data-table');
                                })
                            },
                            error: function (xhr, status, error) {
                                layui.popup.failure(xhr.responseJSON.message);
                            },
                            complete: function() {
                                layer.close(loading);
                            }
                        })
                    });
                }
            });
        })
    </script>
@endpush
