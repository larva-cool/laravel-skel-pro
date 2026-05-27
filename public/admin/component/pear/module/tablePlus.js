/*jshint esversion: 6 */
layui.define(['jquery', 'form', 'util', 'layer', 'table', 'treeTable', 'common', 'popup'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let util = layui.util;
    let layer = layui.layer;
    let table = layui.table;
    let common = layui.common;
    let popup = layui.popup;
    let treeTable = layui.treeTable;

    let tablePlus = {
        // 创建
        createRow: function (url, obj, title, area) {
            layer.open({
                type: 2,
                title: title,
                shade: 0.1,
                area: area ? area : ["85%", "85%"],
                content: url,
                end: function(index) {
                    var tableIns = table.getOptions(obj.config.id);
                    table.reloadData(obj.config.id, {
                        page: {
                            curr: 1,
                            limit: tableIns.page.limit
                        },
                        where: tableIns.where
                    });
                }
            });
        },
        // 查看行
        showRow: function (url, rowObj, title, area) {
            layer.open({
                type: 2,
                title: title,
                shade: 0.1,
                area: area ? area :["90%", "90%"],
                content: url,
                end: function(index) {
                    var tableIns = table.getOptions(rowObj.config.id);
                    table.reloadData(rowObj.config.id, {
                        page: {
                            curr: tableIns.page.curr,
                            limit: tableIns.page.limit
                        },
                        where: tableIns.where
                    });
                }
            });
        },
        // 修改行
        editRow: function (url, rowObj, title, area) {
            layer.open({
                type: 2,
                title: title,
                shade: 0.1,
                area: area ? area : ["85%", "85%"],
                content: rowObj.data.edit_url,
                end: function (index) {
                    var tableIns = table.getOptions(rowObj.config.id);
                    table.reloadData(rowObj.config.id, {
                        page: {
                            curr: tableIns.page.curr,
                            limit: tableIns.page.limit
                        },
                        where: tableIns.where
                    });
                }
            });
        },
        // 删除行
        deleteRow: function (url, rowObj, tips) {
            layer.confirm(tips ? tips : '确定要删除吗？', { icon: 3, title: '提示' }, function (index) {
                let loading = layer.load();
                $.ajax({
                    url: url ? url : rowObj.data.delete_url,
                    dataType: 'json',
                    type: 'delete',
                    success: function (res) {
                        layer.msg(res.message, { icon: 1, time: 1000 }, function () {
                            rowObj.del();
                        });
                    },
                    error: function (xhr, status, error) {
                        layer.msg(xhr.responseJSON.message, { icon: 2, time: 1000 });
                    },
                    complete: function () {
                        layer.close(loading);
                    }
                });
            });
        },
        // 批量删除行
        batchDeleteRow: function (url, rowObj, tips) {
            let checkIds = common.checkField(rowObj, "id");
            if (checkIds === "") {
                popup.warning("未选中数据");
                return false;
            }
            let data = {};
            data["ids"] = checkIds.split(",");
            layer.confirm(tips ? tips : "确定删除选中?", {
                icon: 3,
                title: "提示"
            }, function(index) {
                layer.close(index);
                let loading = layer.load();
                $.ajax({
                    url: url,
                    data: data,
                    dataType: "json",
                    type: "post",
                    success: function(res) {
                        layui.popup.success(res.message, function() {
                            var tableIns = table.getOptions(rowObj.config.id);
                            table.reloadData(rowObj.config.id, {
                                page: {
                                    curr: tableIns.page.curr,
                                    limit: tableIns.page.limit
                                },
                                where: tableIns.where
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        layui.popup.failure(xhr.responseJSON.message);
                    },
                    complete: function() {
                        layer.close(loading);
                    }
                });
            });
        },
        // 图片
        image: function (url, width, height) {
            if (!url) {
                return '';
            }
            return '<img src="' + encodeURI(url) + '" style="max-width:' + width + 'px;max-height:' + height + 'px;"   alt="图片"/>';
        },
        // 开关
        statusSwitch: function (url, d, field, tips) {
            form.on("switch(" + field + ")", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: url,
                    data: {
                        id: data.elem.value,
                        [`${field}`]: data.elem.checked ? 1 : 0,
                    },
                    dataType: "json",
                    type: "post",
                    success: function (res) {
                        layer.msg(res.message, { icon: 1, time: 1000 });
                    },
                    error: function (xhr, status, error) {
                        layer.msg(xhr.responseJSON.message, { icon: 2, time: 1000 }, function () {
                            data.elem.checked = !data.elem.checked;
                            form.render();
                        });
                    },
                    complete: function () {
                        layer.close(loading);
                    }
                });
            });
            let checked = '';
            if (typeof d[field] === "object") {
                checked = d[field].value === 1 ? "checked" : "";
            } else {
                checked = d[field] === 1 ? "checked" : "";
            }
            tips = tips ? tips : "可用|禁用";
            return '<input type="checkbox" title="' + tips + '" value="' + util.escape(d['id']) + '" lay-filter="' + util.escape(field) + '" lay-skin="switch" lay-text="' + util.escape('') + '" ' + checked + '/>';
        },
        // 开关状态
        status: function (d, field) {
            let ret = '';
            if (typeof d[field] === "object") {
                ret = d[field].value === 1 ? "可用" : "禁用";
            } else {
                ret = d[field] === 1 ? "可用" : "禁用";
            }
            return ret;
        },
        // 确认请求
        confirmPost: function (url, data, rowObj, tips) {
            layer.confirm(tips ? tips : '确定吗？', { icon: 3, title: '提示' }, function (index) {
                let loading = layer.load();
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data: data || {},
                    success: function (res) {
                        layer.msg('操作成功！', { icon: 1, time: 1000 }, function () {
                            var tableIns = table.getOptions(rowObj.config.id);
                            table.reloadData(rowObj.config.id, {
                                page: {
                                    curr: tableIns.page.curr,
                                    limit: tableIns.page.limit
                                },
                                where: tableIns.where
                            });
                        });
                    },
                    error: function (xhr, status, error) {
                        popup.failure(xhr.responseJSON.message);
                    },
                    complete: function () {
                        layer.close(loading);
                    }
                });
            });
        },
        // 渲染表格
        render: function (options) {
            let opts = $.extend({
                toolbar: '#table-toolbar',
                page: true,
                limit: 20, // 每页显示的数量
                limits: [15, 20, 30, 50, 100],
                skin: 'line',
                loading: true,
                cellMinWidth: 80,
                defaultToolbar: [{
                    title: '刷新',
                    layEvent: 'refresh',
                    icon: 'layui-icon-refresh',
                    onClick: function (obj) { // 点击事件 - 2.9.12+
                        layui.table.reloadData(obj.config.id, {
                            where: obj.config.where,
                        });
                    }
                }, 'filter', 'print', 'exports'],
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
                },
                error: function (e, msg) {
                    console.log(e, msg);
                    console.log(e.responseJSON);
                }
            }, options);
            var tableIns = table.render(opts);
            // 设置 排序事件
            table.on("sort(" + tableIns.config.id + ")", function (obj) {
                var config = obj.config; // 当前表格配置（包含最新查询条件）
                var params = {};
                if (config.where) {
                    for (var key in config.where) {
                        params[key] = config.where[key];
                    }
                }
                params.sortField = obj.field;
                params.sortOrder = obj.type;
                layui.table.reload("data-table", {
                    initSort: obj,
                    scrollPos: "fixed",
                    where: params,
                });
            });
            // 表格顶部搜索事件
            form.on("submit(table-query)", function (data) {
                table.reloadData(tableIns.config.id, { page: { curr: 1 }, where: data.field });
                return false;
            });
            // 表格顶部搜索重置事件
            form.on("submit(table-reset)", function (data) {
                table.reloadData(tableIns.config.id, { page: { curr: 1 }, where: [] });
            });
            return tableIns;
        },
        // 渲染树
        renderTree: function (options, expandAll) {
            let opts = $.extend({
                elem: '#data-table',
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
                        expandAllDefault: expandAll ? expandAll : false,
                    }
                },
                page: true,
                limit: 100,
                limits: [50, 100],
                toolbar: "#table-toolbar",
                defaultToolbar: [{
                    title: "刷新",
                    layEvent: "refresh",
                    icon: "layui-icon-refresh",
                    onClick: function (obj) { // 点击事件 - 2.9.12+
                        layui.table.reloadData(obj.config.id, {
                            where: obj.config.where,
                        });
                    }
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
            }, options);
            return treeTable.render(opts);
        },
    };

    // 输出模块
    exports('tablePlus', tablePlus);
});
