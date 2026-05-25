/*jshint esversion: 6 */
layui.define(['jquery', 'form', 'util', 'layer', 'table', 'treeTable', 'common', 'popup'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let popup = layui.popup;
    let layer = layui.layer;

    let formPlus = {
        // 保存后关闭弹窗
        save: function (url, filter) {
            form.on("submit(" +filter+ ")", function(data) {
                let loading = layer.load();
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: data.field,
                    success: function(res) {
                        popup.success(res.message, function() {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },
                    error: function(xhr, status, error) {
                        popup.failure(xhr.responseJSON.message);
                    },
                    complete: function() {
                        layer.close(loading);
                    }
                });
                return false;
            });
        }
    };

    // 输出模块
    exports('formPlus', formPlus);
});
