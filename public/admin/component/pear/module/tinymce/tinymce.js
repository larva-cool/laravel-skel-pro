layui.define(['jquery'], function (exports) {
    var $ = layui.$;
    var modFile = layui.cache.modules['tinymce'];
    var modPath = modFile.substr(0, modFile.lastIndexOf('.'));

    //  ----------------  以上代码无需修改  ----------------

    var plugin_filename = 'tinymce.min.js';//插件路径，不包含base_url部分

    var settings = {
        base_url: modPath,
        images_upload_url: '/admin/uploader/tinymce',//图片上传接口，可在option传入，也可在这里修改，option的值优先
        language: 'zh_CN',//语言，可在option传入，也可在这里修改，option的值优先
    };

    //  ----------------  以下代码无需修改  ----------------

    var t = {};

    //初始化
    t.render = function (options, callback) {
        initTinymce();
        var option = initOptions(options, callback);
        var edit = t.get(option.elem);
        if (edit) {
            edit.destroy();
        }
        tinymce.init(option);
        return t.get(option.elem);
    };

    t.init = t.render;

    // 获取ID对应的编辑器对象
    t.get = function (elem) {
        initTinymce();
        if (elem && /^#|\./.test(elem)) {
            var id = elem.substr(1);
            return tinymce.get(id);
        } else {
            return false;
        }
    };

    //重载
    t.reload = function (elem, option, callback) {
        var options = {};
        if (typeof elem === 'string') {
            option.elem = elem;
            options = $.extend({}, option);
        } else if (typeof elem == 'object' && typeof elem.elem === 'string') {
            options = $.extend({}, elem);
            callback = option;
        }

        var optionCache = layui.sessionData('layui-tinymce')[options.elem];
        delete optionCache.init_instance_callback;
        $.extend(optionCache, options);

        return t.render(optionCache, callback);
    };

    function initOptions(option, callback) {
        var form = option.form || {};
        option.base_url = isset(option.base_url) ? option.base_url : settings.base_url;
        option.suffix = isset(option.suffix) ? option.suffix : (plugin_filename.indexOf('.min') > -1 ? '.min' : '');
        option.selector = isset(option.selector) ? option.selector : option.elem;
        option.license_key = isset(option.license_key) ? option.license_key : 'gpl';
        option.promotion = false;
        option.language = isset(option.language) ? option.language : settings.language;
        option.resize = isset(option.resize) ? option.resize : false;
        option.elementpath = isset(option.elementpath) ? option.elementpath : false;
        option.branding = isset(option.branding) ? option.branding : false;
        option.contextmenu_never_use_native = isset(option.contextmenu_never_use_native) ? option.contextmenu_never_use_native : true;
        // 插件
        option.plugins = 'code quickbars preview searchreplace autolink fullscreen image link media codesample table charmap advlist lists wordcount';
        // 选择工具
        option.quickbars_selection_toolbar = isset(option.quickbars_selection_toolbar) ? option.quickbars_selection_toolbar : 'bold italic | quicklink h2 h3 blockquote';
        // 快速插入工具
        option.quickbars_insert_toolbar = isset(option.quickbars_insert_toolbar) ? option.quickbars_insert_toolbar : false;
        // 菜单栏配置
        option.menubar = isset(option.menubar) ? option.menubar : true;
        // 菜单配置
        //option.menu = isset(option.menubar) ? option.menubar : {

        //};
        // 工具栏配置
        option.toolbar = isset(option.toolbar) ? option.toolbar : 'code undo redo | forecolor backcolor bold italic underline strikethrough | alignleft aligncenter alignright alignjustify outdent indent | link bullist numlist image table codesample | formatselect fontselect fontsizeselect';

        option.init_instance_callback = isset(option.init_instance_callback) ? option.init_instance_callback : function (inst) {
            if (typeof callback == 'function') callback(option, inst)
        };

        // 设置上传地址
        option.images_upload_url = isset(option.images_upload_url) ? option.images_upload_url : settings.images_upload_url;
        option.images_upload_credentials = true;

        layui.sessionData('layui-tinymce', {
            key: option.selector,
            value: option
        });
        return option;
    }

    function initTinymce() {
        if (typeof tinymce === 'undefined') {
            $.ajax({//获取插件
                url: settings.base_url + '/' + plugin_filename,
                dataType: 'script',
                cache: true,
                async: false,
            });
        }
    }

    function isset(value) {
        return typeof value !== 'undefined' && value !== null;
    }

    function isEmpty(value) {
        if (typeof value === 'undefined' || value === null || value === '') {
            return true;
        } else if (value instanceof Array && value.length === 0) {
            return true;
        } else if (typeof value === 'object' && Object.keys(value).length === 0) {
            return true;
        }
        return false;
    }

    exports('tinymce', t);
});
