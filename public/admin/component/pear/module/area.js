/*jshint esversion: 6 */
layui.define(['jquery', 'form'], function (exports) {
    "use strict";

    const MOD_NAME = 'area',
        $ = layui.jquery, form = layui.form;

    function loadArea(id, val, elem, placeholder) {
         if (id === false) {
             $(elem).html('<option value="">' + placeholder + '</option>');
             form.render();
         } else {
            $.getJSON('/admin/areas/children', {id: id}, function (res) {
                let html = '<option value="">' + placeholder + '</option>';
                for (let i = 0; i < res.length; i++) {
                    if (res[i]['id'] === val) {
                        html += '<option value="' + res[i]['id'] + '" selected>' + res[i]['name'] + '</option>';
                    } else {

                        html += '<option value="' + res[i]['id'] + '">' + res[i]['name'] + '</option>';
                    }
                }
                $(elem).html(html);
                form.render();
            });
        }
    }

    const area = function () {
        this.v = '1.0.0';
    };

    area.prototype.render = function (opt) {
        opt.prov_elem = opt.prov_elem || '#prov';
        opt.prov_filter = opt.prov_filter || 'prov';
        opt.prov_value = opt.prov_value || '';
        opt.prov_placeholder = opt.prov_placeholder || '请选择省';

        opt.city_elem = opt.city_elem || '';
        opt.city_filter = opt.city_filter || 'city';
        opt.city_value = opt.city_value || '';
        opt.city_placeholder = opt.city_placeholder || '请选择市';

        opt.area_elem = opt.area_elem || '';
        opt.area_filter = opt.area_filter || 'area';
        opt.area_value = opt.area_value || '';
        opt.area_placeholder = opt.area_placeholder || '请选择区';

        // 加载
        loadArea('', parseInt(opt.prov_value), opt.prov_elem, opt.prov_placeholder);
        if (opt.city_elem && opt.city_value) {
            loadArea(parseInt(opt.prov_value), parseInt(opt.city_value), opt.city_elem, opt.city_placeholder);
        }
        if (opt.area_elem && opt.area_value) {
            loadArea(parseInt(opt.city_value), parseInt(opt.area_value), opt.area_elem, opt.area_placeholder);
        }
        if (opt.prov_filter && opt.city_elem) {
            form.on('select(' + opt.prov_filter + ')', function (data) {
                loadArea(data.value === '' ? false : data.value, '', opt.city_elem, opt.city_placeholder);
                if(opt.area_elem) {
                    $(opt.area_elem).html('<option value="">' + opt.area_placeholder + '</option>');
                    form.render();
                }
            });
        }
        if (opt.city_filter && opt.area_elem) {
            form.on('select(' + opt.city_filter + ')', function (data) {
                loadArea(data.value === '' ? false : data.value, '', opt.area_elem, opt.area_placeholder);
            });
        }
        form.render();
    };

    exports(MOD_NAME, new area());
});
