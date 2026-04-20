@extends('admin.layout')

@section('title', '添加智能体')

@section('content')
    <form class="layui-form" action="">
        @csrf
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label required">名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label required">值类型</label>
                        <div class="layui-input-inline">
                            <select name="cast_type">
                                <option value="string">字符串</option>
                                <option value="float">浮点型</option>
                                <option value="bool">布尔型</option>
                                <option value="int">整型</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">Key</label>
                    <div class="layui-input-block">
                        <input type="text" name="key" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">Value</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">参数</label>
                    <div class="layui-input-block">
                        <textarea name="param" placeholder="请输入参数" class="layui-textarea"></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label required">排序</label>
                        <div class="layui-input-inline">
                            <input type="text" name="order" value="0" placeholder="请输入排序值。" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label required">输入类型</label>
                        <div class="layui-input-inline">
                            <select name="input_type">
                                <option value="text">文本框</option>
                                <option value="textarea">文本域</option>
                                <option value="select">下拉选择框</option>
                                <option value="radio">单选框</option>
                                <option value="checkbox">复选框</option>
                                <option value="range">范围选择器</option>
                                <option value="color">颜色选择器</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <input type="text" name="remark" value="" placeholder="请输入备注。" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                    提交
                </button>
                <button type="reset" class="pear-btn pear-btn-md">
                    重置
                </button>
            </div>
        </div>

    </form>
@endsection

@push('scripts')
    <script>
        //提交事件
        layui.use(["form", "popup"], function () {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on("submit(save)", function (data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{route('admin.ai_agents.store')}}"
                    , type: "POST"
                    , dataType: "json"
                    , data: data.field
                    , success: function (res) {
                        popup.success(res.message, function () {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    }
                    , error: function (xhr, status, error) {
                        popup.failure(xhr.responseJSON.message);
                    },
                    complete: function () {
                        layer.close(loading);
                    }
                });
                return false;
            });
        });

    </script>
@endpush
