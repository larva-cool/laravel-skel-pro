layui.define(['element', 'jquery', 'form', 'laytpl'], function (exports) {
    "use strict";

    var element = layui.element,
        $ = layui.jquery,
        form = layui.form, //表单
        laytpl = layui.laytpl,
        moduleName = 'labelSelector', //模块名

        labelSelector = {
            version: '1.0.0',
            config: {
                newLabelIndex: 0,
                totalLabel: 0,
                hasSelected: [],  //选中标签的值
                hasSelectedTitle: [] //选中标签的显示值
            },
            index: layui[moduleName] ? (layui[moduleName].index + 10000) : 0,
            set: function (options) {
                this.config = $.extend(true, {}, this.config, options);
            }
        },
        //构造器
        Class = function (options) {
            this.index = ++labelSelector.index;
            this.config = $.extend(true, {}, labelSelector.config, options);
            this.render();
        },
        tableSelectorWindowHtml = ` <div id="{{=d.windowId}}" class="label-selector-window" style="{{=d.windowStyle}}">
                                        <h2 id="{{=d.headerId}}" class="label-selector-header" style="{{=d.title.style}}">{{=d.title.text}}</h2>
                                    </div>`;

    Class.prototype.render = function () {
        if (!$(this.config.elem)[0]) return;

        this.config.hasSelected = (this.config?.defaultSelected ?? []).map(item => item.toString());
        this.iniLabelSelectorWindow();
        this.iniTab();
        this.listenLabelChecked();
        this.iniButton();
    }

    //初始化窗体
    Class.prototype.iniLabelSelectorWindow = function () {
        let that = this,
            config = that.config,
            index = that.index,
            elem = config.elem,
            windowConfig = config?.window ?? { windowStyle: "", title: { text: "请选择合适的类别", style: "" } };

        let windowDefaultProp = { windowId: "label-selector-window-" + index, headerId: "label-selector-header-" + index };
        $.extend(that.config, windowDefaultProp);
        $.extend(windowConfig, windowDefaultProp);
        laytpl(tableSelectorWindowHtml).render(windowConfig, function (result) {
            $(elem).append(result);
        });
    }

    //初始化Tab
    Class.prototype.iniTab = function () {
        let that = this,
            index = that.index,
            config = that.config,
            checkBoxSkin = (config?.tab?.checkboxSkin) ?? 'primary',
            tabFilter = 'label-selector-' + index,
            checkBoxFilter = 'label-checkbox-filter-' + index,
            tabConfig = $.extend(true, {}, config?.tab ?? {}, { tabFilter: tabFilter }),
            tabHtml = `<div {{=d.isAllowClose ? 'lay-allowclose="true"':''}} id="{{=d.tabFilter}}" class="layui-tab {{=d.tabClass}}" lay-filter="{{=d.tabFilter}}">
                    <ul class="layui-tab-title"></ul><div class="layui-tab-content" style="{{=d.tabContentStyle}}"></div></div>` ;

        $.extend(that.config, { tabFilter: tabFilter, checkBoxFilter: checkBoxFilter });
        laytpl(tabHtml).render(tabConfig, function (result) {
            $(config.elem).find('#' + config.windowId).append(result);
        });
        element.render('tab', tabFilter);

        //动态添加tab
        let tabData = config?.data ?? [],
            defaultSelected = config?.defaultSelected ?? [],
            labelDats = [];
        tabData.forEach(function (tab) {
            let labelData = tab?.label ?? [],
                tabId = tab?.id ?? 0,
                tabTitle = tab?.title ?? '',
                isAllowAddLabel = config?.isAllowAddLabel ?? false,
                labelFormId = 'label-selector-form-' + index + "-" + tabId,
                contentHtml = ['<div class="layui-form label-selector-form" id="' + labelFormId + '">'],
                selectorCheckBoxHtml = `<div class="layui-input-inline"><input type="checkbox" lay-skin="{{=d.checkBoxSkin}}" lay-filter="{{=d.checkBoxFilter}}" title="{{=d.title}}" value="{{=d.value}}" {{d.isChecked?'checked':''}} /></div>`;

            labelData.forEach(function (label) {
                labelDats.push(label);
                config.totalLabel += 1;
                $.extend(label, { checkBoxFilter: checkBoxFilter, checkBoxSkin: checkBoxSkin, isChecked: defaultSelected.includes(label.value) });
                laytpl(selectorCheckBoxHtml).render(label, function (result) {
                    contentHtml.push(result);
                });
            });

            if (isAllowAddLabel) { //允许添加新标签
                let newLabelButtonId = "newLabelButton-" + index + "-" + tabId,
                    addNewLabelButtonHtml = `<div class="layui-input-inline">
                    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary newLabelButton" tab-id={{d.tabId}} id={{=d.newLabelButtonId}}>+ 添加新标签</button>
                    </div>`;
                laytpl(addNewLabelButtonHtml).render({ tabId: tabId, newLabelButtonId: newLabelButtonId }, function (result) {
                    contentHtml.push(result);
                });
                that.dealAddNewLabelEvent(newLabelButtonId, addNewLabelButtonHtml, selectorCheckBoxHtml);
            }

            contentHtml.push('</div>');
            element.tabAdd(tabFilter, { title: tabTitle, id: tabId, content: contentHtml.join(''), change: true });
        });

        //初始化默认到第一个tab项
        element.tabChange(tabFilter, tabData[0]?.id ?? 0);

        //获取初始化选中的标签的title
        defaultSelected.forEach(value => {
            let result = labelDats.filter(item => item.value === value).map(item => item.title);
            if (result) {
                let title = result[0] ?? '';
                that.config.hasSelectedTitle.push(title);
            }
        });
        form.render('checkbox');    //渲染checkbox
        that.renderHeaderSelectedRatioTip();    //渲染选中结果

        // tab 切换事件
        element.on('tab(' + tabFilter + ')', function (data) {
            let tabId = $(this).attr("lay-id");
            if (config?.tab?.change) config.tab.change(this, tabId, data);
        });

        // tab 删除事件
        element.on('tabDelete(' + tabFilter + ')', function (data) {
            if (config?.tab?.delete) config.tab.delete(this, data);
        });
    }

    Class.prototype.dealAddNewLabelEvent = function (newLabelButtonId, addNewLabelButtonHtml, selectorCheckBoxHtml) {
        let that = this,
            index = that.index,
            config = that.config,
            tabData = config?.data ?? [],
            checkBoxSkin = (config?.tab?.checkboxSkin) ?? 'primary',
            checkBoxFilter = config.checkBoxFilter,
            newLabelInputId = "newLabelInput-" + index;

        //监听添加按钮点击事件
        $(document).off('click', '#' + newLabelButtonId).on('click', '#' + newLabelButtonId, function () {
            let tabId = $(this).attr("tab-id");
            $(this).parent().empty().append('<input type="text" class="layui-input" style="height:30px;width:80px" id="' + newLabelInputId + '" tab-id="' + tabId + '"/>');
            $('#' + newLabelInputId).focus();
        });

        //监听输入框回车
        $(document).off('keydown', '#' + newLabelInputId).on('keydown', '#' + newLabelInputId, function (e) {
            if (e.keyCode === 13) $(this).blur();
        });

        //监听输入框失去焦点
        $(document).off('blur', '#' + newLabelInputId).on('blur', '#' + newLabelInputId, function () {
            dealNewTagInput(this);
        });

        function dealNewTagInput(inputEvent) {
            let tabId = $(inputEvent).attr("tab-id"),
                newLabelTitle = $(inputEvent).val();
            $(inputEvent).parent().remove(); //清除掉当前输入框

            if (newLabelTitle && newLabelTitle != '') {
                let newLabelValue = "new-label-" + (++config.newLabelIndex);
                if (config?.addNewLabel) {
                    let labelResult = config.addNewLabel(tabId, newLabelTitle),
                        addLabelResult = labelResult?.success ?? true;
                    if (addLabelResult) {
                        newLabelValue = labelResult?.labelValue ?? newLabelValue;
                        addLabelCheckBox(tabId, newLabelTitle, newLabelValue);
                    }
                } else {
                    addLabelCheckBox(tabId, newLabelTitle, newLabelValue);
                }
            }
            laytpl(addNewLabelButtonHtml).render({ tabId: tabId, newLabelButtonId: newLabelButtonId }, function (result) {
                $("#label-selector-form-" + index + "-" + tabId).append(result);
            });
            that.renderHeaderSelectedRatioTip();
        }

        function addLabelCheckBox(tabId, newLabelTitle, newLabelValue) {
            let labelData = { title: newLabelTitle, value: newLabelValue, checkBoxSkin: checkBoxSkin, checkBoxFilter: checkBoxFilter, isChecked: false };
            laytpl(selectorCheckBoxHtml).render(labelData, function (result) {
                $("#label-selector-form-" + index + "-" + tabId).append(result);
                form.render('checkbox'); //渲染checkbox
            });

            //维护原来的data
            for (let i = 0; i < tabData.length; i++) {
                let id = tabData[i].id;
                if (id != tabId) continue;

                (tabData[i]?.label ?? []).push(labelData);
            }
            ++that.config.totalLabel; //添加后总标签数+1
        }
    }

    //监听checkbox点击事件
    Class.prototype.listenLabelChecked = function () {
        let that = this,
            config = that.config,
            checkBoxFilter = config.checkBoxFilter;

        //监听checkbox
        form.on('checkbox(' + checkBoxFilter + ')', function (data) {
            let elem = data.elem,
                value = elem.value,
                title = elem.title,
                isAllowSelectTip = config?.isAllowSelectTip ?? true,
                hasSelected = config.hasSelected,
                hasSelectedTitle = config.hasSelectedTitle;

            if (elem.checked) {
                hasSelected.push(value);
                hasSelectedTitle.push(title);
                if (isAllowSelectTip)
                    layer.msg('您已经选中【' + title + '】标签！');
            } else {
                let eleIndex = hasSelected.indexOf(value);
                if (eleIndex >= 0) {
                    hasSelected.splice(eleIndex, 1);
                    hasSelectedTitle.splice(eleIndex, 1);
                }

                if (isAllowSelectTip) layer.msg('您已经取消选择【' + title + '】标签！');
            }
            that.renderHeaderSelectedRatioTip();
            //调用外部点击处理事件
            if (config?.clicked) {
                config.clicked(elem, hasSelected, hasSelectedTitle);
            }
        });
    }

    //渲染选择比例提示
    Class.prototype.renderHeaderSelectedRatioTip = function () {
        let that = this,
            config = that.config,
            isAllowShowSelectedRatio = config?.window?.title?.isAllowShowSelectedRatio ?? true,
            header = $("#" + config.headerId),
            hasSelectedLength = config.hasSelected.length,
            totalLabel = config.totalLabel,
            rationHtml = '<span style="{{=d.ratioStyle}}">(已选 ' + hasSelectedLength + ' / ' + totalLabel + ')</span>';

        if (isAllowShowSelectedRatio) {
            $(header).find('span').remove();
            laytpl(rationHtml).render({ ratioStyle: config?.window?.title?.ratioStyle ?? "" }, function (result) {
                $(header).append(result);
            });
        }
    }

    //初始化按钮
    Class.prototype.iniButton = function () {
        if (!(this.config?.window?.btn ?? false)) return;

        let that = this,
            config = that.config,
            index = that.index,
            windowId = config.windowId,
            confirmButtonId = 'label-selector-confirm-button-' + index,
            cancelButtonId = 'label-selector-cancel-button-' + index,
            buttonHtml = `  <div class='button-zone'>
                                    <div class="button-zone-wrapper">
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="`+ cancelButtonId + `">取消</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" id="`+ confirmButtonId + `">确定</button>
                                    </div>
                            </div>`;

        $('#' + windowId).append(buttonHtml);

        $("#" + confirmButtonId).click(function () {
            if (config?.confirm) {
                config.confirm(config.hasSelected, config.hasSelectedTitle);
            }
        });

        $("#" + cancelButtonId).click(function () {
            if (config?.cancel) {
                config.cancel();
            }
        });
    }

    labelSelector.render = function (options) {
        var inst = new Class(options);
    }

    exports('labelSelector', labelSelector);
});
