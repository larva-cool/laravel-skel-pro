@extends('admin.layout')

@section('title', '附件上传')

@section('content')
    <form class="layui-form" method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="mainBox">
            <div class="main-container">
                <div class="layui-upload">
                    <div class="layui-upload-list">
                        <table class="layui-table">
                            <colgroup>
                                <col style="min-width: 100px;">
                                <col width="150">
                                <col width="260">
                                <col width="150">
                            </colgroup>
                            <thead>
                            <th>文件名</th>
                            <th>大小</th>
                            <th>上传进度</th>
                            <th>操作</th>
                            </thead>
                            <tbody id="ID-upload-files-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom">
            <div class="button-container">
                <button type="button" class="layui-btn layui-btn-normal" id="ID-upload-files">选择文件
                </button>
                <button type="button" class="layui-btn" id="ID-upload-files-action">开始上传</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        layui.use(['upload','element','popup'],function () {
            let upload = layui.upload;
            let element = layui.element;
            let popup = layui.popup;
            let $ = layui.$;
            // 制作多文件上传表格
            let uploadListIns = upload.render({
                elem: '#ID-upload-files',
                elemList: $('#ID-upload-files-list'), // 列表元素对象
                url: '{{ route('admin.attachments.store') }}', // 实际使用时改成您自己的上传接口即可。
                accept: 'file',
                multiple: true,
                auto: false,
                //exts: "{{settings('upload.allow_extension')}}".replace(",", "|"),
                bindAction: '#ID-upload-files-action',
                choose: function (obj) {
                    let that = this;
                    let files = this.files = obj.pushFile(); // 将每次选择的文件追加到文件队列
                    // 读取本地文件
                    obj.preview(function (index, file, result) {
                        let tr = $(['<tr id="upload-' + index + '">',
                            '<td>' + file.name + '</td>',
                            '<td>' + (file.size / 1024).toFixed(1) + 'kb</td>',
                            '<td><div class="layui-progress" lay-filter="progress-' + index + '"><div class="layui-progress-bar" lay-percent=""></div></div></td>',
                            '<td>',
                            '<button type="button" class="layui-btn layui-btn-xs reload layui-hide">重传</button>',
                            '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger delete">删除</button>',
                            '</td>',
                            '</tr>'].join(''));

                        // 单个重传
                        tr.find('.reload').on('click', function () {
                            obj.upload(index, file);
                        });

                        // 删除
                        tr.find('.delete').on('click', function () {
                            delete files[index]; // 删除对应的文件
                            tr.remove(); // 删除表格行
                            // 清空 input file 值，以免删除后出现同名文件不可选
                            uploadListIns.config.elem.next()[0].value = '';
                        });

                        that.elemList.append(tr);
                        element.render('progress'); // 渲染新加的进度条组件
                    });
                },
                done: function (res, index, upload) { // 成功的回调
                    let that = this;
                    if (res.code === 0) { // 上传成功
                        let tr = that.elemList.find('tr#upload-' + index)
                        let tds = tr.children();
                        tds.eq(3).html(''); // 清空操作
                        delete this.files[index]; // 删除文件队列已经上传成功的文件
                        return;
                    }
                    this.error(index, upload);
                },
                allDone: function (obj) { // 多文件上传完毕后的状态回调
                    console.log(obj);
                    if(obj.total == obj.successful){
                        popup.success("上传完成", function () {
                             parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    }
                    return;
                },
                error: function (index, upload) { // 错误回调
                    let that = this;
                    let tr = that.elemList.find('tr#upload-' + index);
                    let tds = tr.children();
                    // 显示重传
                    tds.eq(3).find('.reload').removeClass('layui-hide');
                },
                progress: function (n, elem, e, index) { // 注意：index 参数为 layui 2.6.6 新增
                    element.progress('progress-' + index, n + '%'); // 执行进度条。n 即为返回的进度百分比
                }
            });
        });
    </script>
@endpush
