@extends('admin.layout')

@section('title', '添加智能体')

@section('content')
    <form class="layui-form" action="">
        @csrf
        @method('PUT')
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label required">名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" value="{{ $item->name }}" required lay-verify="required" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label required">值类型</label>
                        <div class="layui-input-inline">
                            <select name="cast_type">
                                <option value="string" @selected($item->cast_type == 'string')>字符串</option>
                                <option value="float" @selected($item->cast_type == 'float')>浮点型</option>
                                <option value="bool" @selected($item->cast_type == 'bool')>布尔型</option>
                                <option value="int" @selected($item->cast_type == 'int')>整型</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">Key</label>
                    <div class="layui-input-block">
                        <input type="text" name="key" value="{{ $item->key }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">Value</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" value="{{ $item->value }}" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">参数</label>
                    <div class="layui-input-block">
                        <textarea name="param" placeholder="请输入参数" class="layui-textarea">{{ $item->param }}</textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label required">排序</label>
                        <div class="layui-input-inline">
                            <input type="text" name="order" value="{{ $item->order }}" placeholder="请输入排序值。" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label required">输入类型</label>
                        <div class="layui-input-inline">
                            <select name="input_type">
                                <option value="text" @selected($item->input_type == 'text')>文本框</option>
                                <option value="textarea" @selected($item->input_type == 'textarea')>文本域</option>
                                <option value="select" @selected($item->input_type == 'select')>下拉选择框</option>
                                <option value="radio" @selected($item->input_type == 'radio')>单选框</option>
                                <option value="checkbox" @selected($item->input_type == 'checkbox')>复选框</option>
                                <option value="range" @selected($item->input_type == 'range')>范围选择器</option>
                                <option value="color" @selected($item->input_type == 'color')>颜色选择器</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <input type="text" name="remark" value="{{ $item->remark }}" placeholder="请输入备注。" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                        lay-filter="save">
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
                    url: "{{route('admin.settings.update', $item)}}",
                    type: "PUT",
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
                    complete: function() {
                        layer.close(loading);
                    }
                });
                return false;
            });
        });
    </script>
@endpush
