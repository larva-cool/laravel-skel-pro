/*jshint esversion: 6 */
layui.define(['jquery', 'form', 'util', 'layer'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let util = layui.util;
    let layer = layui.layer;

    let tablePlus = {
        // 删除行
        deleteRow: function (url, rowObj) {
            layer.confirm('确定要删除吗？', {icon: 3, title: '提示'}, function (index) {
                let loading = layer.load();
                $.ajax({
                    url: url ? url : rowObj.data.delete_url,
                    dataType: 'json',
                    type: 'delete',
                    success: function (res) {
                        layer.msg(res.message, {icon: 1, time: 1000}, function () {
                            rowObj.del();
                        });
                    },
                    error: function (xhr, status, error) {
                        layer.msg(xhr.responseJSON.message, {
                            icon: 2,
                            time: 1000
                        });
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
        statusSwitch: function (url, d, field) {
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
                        layer.msg(res.message, {
                            icon: 1,
                            time: 1000
                        });
                    },
                    error: function (xhr, status, error) {
                        layer.msg(xhr.responseJSON.message, {
                            icon: 2,
                            time: 1000
                        }, function () {
                            data.elem.checked = !data.elem.checked;
                            form.render();
                        });
                    },
                    complete: function() {
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
            return '<input type="checkbox" title="可用|禁用" value="' + util.escape(d['id']) + '" lay-filter="' + util.escape(field) + '" lay-skin="switch" lay-text="' + util.escape('') + '" ' + checked + '/>';
        },
    };

    // 输出模块
    exports('tablePlus', tablePlus);
});
