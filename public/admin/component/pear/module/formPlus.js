/*jshint esversion: 6 */
layui.define(["jquery", "form", "layer", "popup", "xmSelect"], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let popup = layui.popup;
    let layer = layui.layer;
    let xmSelect = layui.xmSelect;

    let formPlus = {
        // xm-select 下拉选择
        select: function (url, eleId, tips) {
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success: function (res) {
                    let value = $("#roles").attr("value");
                    let initValue = value ? value.split(",") : [];
                    xmSelect.render({
                        el: "#" + eleId,
                        name: eleId,
                        tips: tips,
                        initValue: initValue,
                        empty: '呀, 没有数据呢',
                        height: 'auto',
                        data: res,
                        layVerify: "required",
                        toolbar: { show: true, list: ["ALL", "CLEAR", "REVERSE"] },
                    });
                },
                error: function (xhr, status, error) {
                    popup.failure(xhr.responseJSON.message);
                }
            });
        },
        // 保存后关闭弹窗
        save: function (url, filter) {
            form.on("submit(" + filter + ")", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: data.field,
                    success: function (res) {
                        popup.success(res.message, function () {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },
                    error: function (xhr, status, error) {
                        popup.failure(xhr.responseJSON.message);
                    },
                    complete: function () {
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
