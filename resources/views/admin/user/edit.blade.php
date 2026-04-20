@extends('admin.layout')

@section('title', '修改用户')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" method="POST">
                @method('PUT')
                @csrf
                <div class="layui-tabs">
                    <ul class="layui-tabs-header">
                        <li class="layui-this">基础信息</li>
                        <li class="">个人资料</li>
                        <li class="">扩展信息</li>
                    </ul>
                    <div class="layui-tabs-body">
                        <!-- 基础信息 -->
                        <div class="layui-tabs-item layui-show">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label required">登录账号</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="username" lay-verify="required" value="{{$item->username}}" placeholder="请输入登录账号" autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label required">邮箱</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="email" lay-verify="email" value="{{$item->email}}" placeholder="请输入登录邮箱" autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label required">手机号</label>
                                    <div class="layui-input-inline layui-input-wrap">
                                        <input type="tel" name="phone" lay-verify="required|phone" value="{{$item->phone}}" autocomplete="off" lay-reqtext="请填写手机号" class="layui-input">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label required">昵称</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="name" value="{{$item->name}}" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">生日</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="profile[birthday]" lay-verify="date" placeholder="yyyy-MM-dd" id="birthday" value="{{$item->profile->birthday}}" class="layui-input">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">性别</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="profile[gender]" value="1" title="男" @checked($item->profile->gender=1)>
                                    <input type="radio" name="profile[gender]" value="2" title="女" @checked($item->profile->gender=2)>
                                    <input type="radio" name="profile[gender]" value="0" title="保密" @checked($item->profile->gender=0)>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">地区</label>
                                <div class="layui-input-block" id="area"></div>
                                <div class="layui-input-inline">
                                    <select name="quiz1">
                                        <option value="">请选择省</option>
                                        <option value="浙江" selected>浙江省</option>
                                        <option value="你的工号">江西省</option>
                                        <option value="你最喜欢的老师">福建省</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <select name="quiz2">
                                        <option value="">请选择市</option>
                                        <option value="杭州">杭州</option>
                                        <option value="宁波" disabled>宁波</option>
                                        <option value="温州">温州</option>
                                        <option value="温州">台州</option>
                                        <option value="温州">绍兴</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <select name="quiz3">
                                        <option value="">请选择县/区</option>
                                        <option value="西湖区">西湖区</option>
                                        <option value="余杭区">余杭区</option>
                                        <option value="拱墅区">临安市</option>
                                    </select>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">新密码</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="password" placeholder="" autocomplete="off" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">留空不修改密码。</div>
                                </div>
                            </div>

                            <div class="layui-form-item layui-form-text">
                                <label class="layui-form-label">个人介绍</label>
                                <div class="layui-input-block">
                                    <textarea name="intro" placeholder="请输入内容" class="layui-textarea"></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- 个人资料 -->
                        <div class="layui-tabs-item">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">个人网站</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="profile[website]" value="{{$item->profile->website}}" class="layui-input">
                                    </div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-form-item layui-form-text">
                                    <label class="layui-form-label">个性签名</label>
                                    <div class="layui-input-block">
                                        <textarea name="bio" placeholder="请输入内容" class="layui-textarea"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 扩展信息 -->
                        <div class="layui-tabs-item">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label required">邀请码</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="extra[invite_code]" value="{{$item->extra->invite_code}}" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label required">用户名修改次数</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="extra[username_change_count]" value="{{$item->extra->username_change_count}}" class="layui-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // 字段 头像 avatar
        layui.use(["form", "http", 'laydate', 'labelSelector'], function () {
            let form = layui.form;
            let http = layui.http;
            let laydate = layui.laydate;
            let labelSelector = layui.labelSelector;

            labelSelector.render({
                elem: "#area",
            });

            laydate.render({
                elem: "#birthday",
                type: 'date'
            });

            //提交事件
            form.on("submit(save)", function (data) {
                console.log(data.field);
                http.formPost("{{$update_url}}", data.field);
                return false;
            });
        });
    </script>
@endpush
