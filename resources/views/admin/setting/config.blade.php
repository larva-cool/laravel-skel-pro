@extends('admin.layout')

@section('title', '系统配置')

@section('content')
    <style>
        .layui-form-label {
            width: 120px;
        }

        .layui-input-block input {
            width: 500px;
        }

        .layui-form-item .layui-input-inline {
            width: 350px;
        }
    </style>
    <div class="pear-container">
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form">
                    <div class="layui-tabs">
                        <ul class="layui-tabs-header">
                            <li lay-id="baseInfo">基本信息</li>
                            <li lay-id="user">会员设置</li>
                            <li lay-id="sms">短信设置</li>
                            <li lay-id="mail">邮件设置</li>
                            <li lay-id="upload">上传设置</li>
                            <li lay-id="openai">OpenAI 配置</li>
                            <li lay-id="vod">点播设置</li>
                            <li lay-id="feed">Feed流</li>
                        </ul>
                        <div class="layui-tabs-body">
                            <!--基本信息-->
                            <div class="layui-tabs-item" lay-id="baseInfo">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">网站地址</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[url]" value="{{ $settings['system.url'] ?? '' }}"
                                            placeholder="请输入网站地址" class="layui-input">
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">H5 地址</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[m_url]"
                                            value="{{ $settings['system.m_url'] ?? '' }}" placeholder="请输入H5地址"
                                            class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label required">网站标题</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[title]"
                                            value="{{ $settings['system.title'] ?? '' }}" placeholder="请输入网站标题（一般不超过80个字符）"
                                            required lay-verify="required" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">网站标题（一般不超过80个字符）。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">网站关键词</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[keywords]"
                                            value="{{ $settings['system.keywords'] ?? '' }}"
                                            placeholder="请输入网站关键词（一般不超过100个字符）" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">网站关键词（一般不超过100个字符）。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">网站描述</label>
                                    <div class="layui-input-inline">
                                        <textarea name="system[description]" placeholder="请输入网站描述（一般不超过200个字符）" class="layui-textarea">{{ $settings['system.description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">网站描述（一般不超过200个字符）。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">ICP备案号</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[icp_beian]"
                                            value="{{ $settings['system.icp_beian'] ?? '' }}" placeholder="请输入ICP备案号"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">信产部备案号。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">公安备案号</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[police_beian]"
                                            value="{{ $settings['system.police_beian'] ?? '' }}" placeholder="请输入公安备案号"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">公安部备案号。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">服务邮箱</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[support_email]"
                                            value="{{ $settings['system.support_email'] ?? '' }}" placeholder="请输入服务邮箱"
                                            class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">法律邮箱</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="system[lawyer_email]"
                                            value="{{ $settings['system.lawyer_email'] ?? '' }}" placeholder="请输入法务邮箱"
                                            class="layui-input">
                                    </div>
                                </div>
                            </div>
                            <!--会员设置-->
                            <div class="layui-tabs-item" lay-id="user">
                                <fieldset class="layui-elem-field">
                                    <legend>注册设置</legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启注册</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_register]" value="1"
                                                    title="开启" @checked($settings['user.enable_register'] == 1)>
                                                <input type="radio" name="user[enable_register]" value="0"
                                                    title="关闭" @checked($settings['user.enable_register'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法注册。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启手机注册</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_phone_register]" value="1"
                                                    title="开启" @checked($settings['user.enable_phone_register'] == 1)>
                                                <input type="radio" name="user[enable_phone_register]" value="0"
                                                    title="关闭" @checked($settings['user.enable_phone_register'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过手机号注册。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启邮箱注册</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_email_register]" value="1"
                                                    title="开启" @checked($settings['user.enable_email_register'] == 1)>
                                                <input type="radio" name="user[enable_email_register]" value="0"
                                                    title="关闭" @checked($settings['user.enable_email_register'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过邮箱注册。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">注册限速</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="user[register_throttle]"
                                                    value="{{ $settings['user.register_throttle'] ?? '' }}"
                                                    placeholder="请输入注册限速" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">
                                                1分钟5次，则输入5,1
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="layui-elem-field">
                                    <legend>登录设置</legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启手机登录</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_phone_login]" value="1"
                                                    title="开启" @checked($settings['user.enable_phone_login'] == 1)>
                                                <input type="radio" name="user[enable_phone_login]" value="0"
                                                    title="关闭" @checked($settings['user.enable_phone_login'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过手机号登录。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启密码登录</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_password_login]" value="1"
                                                    title="开启" @checked($settings['user.enable_password_login'] == 1)>
                                                <input type="radio" name="user[enable_password_login]" value="0"
                                                    title="关闭" @checked($settings['user.enable_password_login'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过密码登录。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启微信登录</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_wechat_login]" value="1"
                                                    title="开启" @checked($settings['user.enable_wechat_login'] == 1)>
                                                <input type="radio" name="user[enable_wechat_login]" value="0"
                                                    title="关闭" @checked($settings['user.enable_wechat_login'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过微信登录。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">开启苹果登录</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[enable_apple_login]" value="1"
                                                    title="开启" @checked($settings['user.enable_apple_login'] == 1)>
                                                <input type="radio" name="user[enable_apple_login]" value="0"
                                                    title="关闭" @checked($settings['user.enable_apple_login'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">关闭后，用户将无法通过苹果登录。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label required">单设备登录</label>
                                            <div class="layui-input-inline">
                                                <input type="radio" name="user[only_one_device_login]" value="1"
                                                    title="开启" @checked($settings['user.only_one_device_login'] == 1)>
                                                <input type="radio" name="user[only_one_device_login]" value="0"
                                                    title="关闭" @checked($settings['user.only_one_device_login'] == 0)>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">开启后，用户在其他设备的登录将会退出。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">登录限速</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="user[login_throttle]"
                                                    value="{{ $settings['user.login_throttle'] ?? '' }}"
                                                    placeholder="请输入登录限速" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">
                                                1分钟5次，则输入5,1
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="layui-elem-field">
                                    <legend>其他设置</legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">用户名修改</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="user[username_change]"
                                                    value="{{ $settings['user.username_change'] ?? '' }}"
                                                    placeholder="允许用户名修改次数" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">
                                                用户名最大允许修改的次数，0表示不允许修改。
                                            </div>
                                        </div>

                                        <div class="layui-form-item">
                                            <label class="layui-form-label">令牌有效期</label>
                                            <div class="layui-input-inline">
                                                <div class="layui-input-group">
                                                    <input type="text" name="user[token_expiration]"
                                                        value="{{ $settings['user.token_expiration'] ?? '' }}"
                                                        placeholder="请输入令牌有效期" class="layui-input">
                                                    <div class="layui-input-split layui-input-suffix">
                                                        分钟
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">
                                                令牌有效期，单位为分钟。
                                            </div>
                                        </div>

                                        <div class="layui-form-item">
                                            <label class="layui-form-label">积分有效期</label>
                                            <div class="layui-input-inline">
                                                <div class="layui-input-group">
                                                    <input type="text" name="user[point_expiration]"
                                                        value="{{ $settings['user.point_expiration'] ?? '' }}"
                                                        placeholder="请输入积分有效期" class="layui-input">
                                                    <div class="layui-input-split layui-input-suffix">
                                                        天
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">
                                                积分过期后，自动回收
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <!--短信设置-->
                            <div class="layui-tabs-item" lay-id="sms">
                                <fieldset class="layui-elem-field">
                                    <legend>火山云<主要>
                                    </legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">短信区域</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[region_id]"
                                                    value="{{ $settings['sms.region_id'] ?? '' }}" placeholder="请输入短信区域"
                                                    class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">国内节点 cn-north-1，国外节点
                                                ap-singapore-1，不填或填错，默认使用国内节点。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">消息组帐号</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[sms_account]"
                                                    value="{{ $settings['sms.sms_account'] ?? '' }}"
                                                    placeholder="请输入消息组帐号" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">请输入消息组帐号。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">短信签名</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[sign_name]"
                                                    value="{{ $settings['sms.sign_name'] ?? '' }}" placeholder="请输入短信签名"
                                                    class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">请输入短信签名。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">短信模板ID</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[template_id]"
                                                    value="{{ $settings['sms.template_id'] ?? '' }}"
                                                    placeholder="请输入短信模板ID" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">请输入短信模板ID。</div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="layui-elem-field">
                                    <legend>阿里云<备用>
                                    </legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">短信签名</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[aliyun_sign_name]"
                                                    value="{{ $settings['sms.aliyun_sign_name'] ?? '' }}"
                                                    placeholder="请输入短信签名" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">请输入短信签名。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">短信模板ID</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms[aliyun_template_id]"
                                                    value="{{ $settings['sms.aliyun_template_id'] ?? '' }}"
                                                    placeholder="请输入短信模板ID" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">请输入短信模板ID。</div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="layui-elem-field">
                                    <legend>短信设置</legend>
                                    <div class="layui-field-box">
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">有效期</label>
                                            <div class="layui-input-inline">
                                                <div class="layui-input-group">
                                                    <input type="text" name="sms_captcha[duration]"
                                                        value="{{ $settings['sms_captcha.duration'] ?? '' }}"
                                                        placeholder="短信验证码有效期" class="layui-input">
                                                    <div class="layui-input-split layui-input-suffix">
                                                        分钟
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">短信验证码有效期，单位为分钟。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">验证码长度</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms_captcha[length]"
                                                    value="{{ $settings['sms_captcha.length'] ?? '' }}"
                                                    placeholder="请输入验证码长度" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">短信验证码长度，单位为数字。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">两次获取间隔</label>
                                            <div class="layui-input-inline">
                                                <div class="layui-input-group">
                                                    <input type="text" name="sms_captcha[wait_time]"
                                                        value="{{ $settings['sms_captcha.wait_time'] ?? '' }}"
                                                        placeholder="请输入两次获取间隔" class="layui-input">
                                                    <div class="layui-input-split layui-input-suffix">
                                                        秒
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="layui-form-mid layui-text-em">两次获取验证码间隔，单位为秒。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">允许尝试次数</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms_captcha[test_limit]"
                                                    value="{{ $settings['sms_captcha.test_limit'] ?? '' }}"
                                                    placeholder="请输入最大允许错误次数" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">验证码最大允许错误次数，超过后验证码失效。
                                            </div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">IP限制</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms_captcha[ip_count]"
                                                    value="{{ $settings['sms_captcha.ip_count'] ?? '' }}"
                                                    placeholder="请输入每个IP地址每小时最大获取验证码次数" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">每个IP地址每小时最大获取验证码次数。</div>
                                        </div>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label">手机号限制</label>
                                            <div class="layui-input-inline">
                                                <input type="text" name="sms_captcha[phone_count]"
                                                    value="{{ $settings['sms_captcha.phone_count'] ?? '' }}"
                                                    placeholder="请输入每个手机号每小时最大获取验证码次数" class="layui-input">
                                            </div>
                                            <div class="layui-form-mid layui-text-em">每个手机号每小时最大获取验证码次数。</div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <!--邮件设置-->
                            <div class="layui-tabs-item" lay-id="mail">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">有效期</label>
                                    <div class="layui-input-inline">
                                        <div class="layui-input-group">
                                            <input type="text" name="email_captcha[duration]"
                                                value="{{ $settings['email_captcha.duration'] ?? '' }}"
                                                placeholder="邮件验证码有效期" class="layui-input">
                                            <div class="layui-input-split layui-input-suffix">
                                                分钟
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">邮件验证码有效期，单位为分钟。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">验证码长度</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="email_captcha[length]"
                                            value="{{ $settings['email_captcha.length'] ?? '' }}" placeholder="请输入验证码长度"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">邮件验证码长度，单位为数字。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">两次获取间隔</label>
                                    <div class="layui-input-inline">
                                        <div class="layui-input-group">
                                            <input type="text" name="email_captcha[wait_time]"
                                                value="{{ $settings['email_captcha.wait_time'] ?? '' }}"
                                                placeholder="请输入两次获取间隔" class="layui-input">
                                            <div class="layui-input-split layui-input-suffix">
                                                秒
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">两次获取验证码间隔，单位为秒。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">允许尝试次数</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="email_captcha[test_limit]"
                                            value="{{ $settings['email_captcha.test_limit'] ?? '' }}"
                                            placeholder="请输入最大允许错误次数" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">验证码最大允许错误次数，超过后验证码失效。
                                    </div>
                                </div>
                            </div>
                            <!--上传设置-->
                            <div class="layui-tabs-item" lay-id="upload">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">默认驱动</label>
                                    <div class="layui-input-inline">
                                        <select name="upload[storage]">
                                            @foreach ($disks as $disk)
                                                <option value="{{ $disk }}" @selected($settings['upload.storage'] == $disk)>
                                                    {{ $disk }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">上传文件默认驱动。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">命名规则</label>
                                    <div class="layui-input-inline">
                                        <select name="upload[name_rule]">
                                            <option value="unique" @selected($settings['upload.name_rule'] == 'unique')>随机命名</option>
                                            <option value="datetime" @selected($settings['upload.name_rule'] == 'datetime')>按时间戳</option>
                                            <option value="sequence" @selected($settings['upload.name_rule'] == 'sequence')>原始顺序</option>
                                        </select>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">上传文件的命名规则。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">允许类型</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="upload[allow_extension]"
                                            value="{{ $settings['upload.allow_extension'] ?? '' }}"
                                            placeholder="请输入允许上传的文件类型" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">允许上传的文件类型，多个类型用逗号分隔。</div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">允许的视频类型</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="upload[allow_video_extension]"
                                            value="{{ $settings['upload.allow_video_extension'] ?? '' }}"
                                            placeholder="请输入允许上传的文件类型" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">允许上传的视频类型，多个类型用逗号分隔。</div>
                                </div>
                            </div>
                            <!--OpenAI 配置-->
                            <div class="layui-tabs-item" lay-id="openai">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">Base URI</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="openai[base_uri]"
                                            value="{{ $settings['openai.base_uri'] ?? '' }}" placeholder="请输入Base URI"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">OpenAI Base URI。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">Organization</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="openai[organization]"
                                            value="{{ $settings['openai.organization'] ?? '' }}"
                                            placeholder="请输入Organization" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">OpenAI Organization。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">Project</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="openai[project]"
                                            value="{{ $settings['openai.project'] ?? '' }}" placeholder="请输入Project"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">OpenAI Project。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">API Key</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="openai[api_key]"
                                            value="{{ $settings['openai.api_key'] ?? '' }}" placeholder="请输入API Key"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">OpenAI API Key。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">请求超时时间</label>
                                    <div class="layui-input-inline">
                                        <div class="layui-input-group">
                                            <input type="text" name="openai[request_timeout]"
                                                value="{{ $settings['openai.request_timeout'] ?? '' }}"
                                                placeholder="请求超时时间" class="layui-input">
                                            <div class="layui-input-split layui-input-suffix">
                                                秒
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">请求超时时间，单位为秒。</div>
                                </div>
                            </div>
                            <!--点播设置-->
                            <div class="layui-tabs-item" lay-id="vod">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">视频空间</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="vod[space_name]"
                                            value="{{ $settings['vod.space_name'] ?? '' }}" placeholder="请输入视频空间名称"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">在视频点播控制台创建的空间的名称</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">视频区域</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="vod[region]"
                                            value="{{ $settings['vod.region'] ?? '' }}" placeholder="请输入视频区域"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">视频空间所属区域，例如：cn-north-1。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">APP ID</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="vod[app_id]"
                                            value="{{ $settings['vod.app_id'] ?? '' }}" placeholder="请输入APP ID"
                                            class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">在视频点播控制台应用管理页面创建的应用的
                                        AppID。视频点播的质量监控等都是以这个参数来区分业务方的，务必正确填写。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">Web/H5 APP ID</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="vod[web_app_id]"
                                            value="{{ $settings['vod.web_app_id'] ?? '' }}"
                                            placeholder="请输入Web/H5 APP ID" class="layui-input">
                                    </div>
                                    <div class="layui-form-mid layui-text-em">在视频点播控制台应用管理页面创建的应用的
                                        AppID。视频点播的质量监控等都是以这个参数来区分业务方的，务必正确填写。</div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">播放有效期</label>
                                    <div class="layui-input-inline">
                                        <div class="layui-input-group">
                                            <input type="text" name="vod[token_expire]"
                                                value="{{ $settings['vod.token_expire'] ?? '' }}"
                                                placeholder="请输入播放签名有效期" class="layui-input">
                                            <div class="layui-input-split layui-input-suffix">
                                                秒
                                            </div>
                                        </div>
                                    </div>
                                    <div class="layui-form-mid layui-text-em">播放地址签名有效期，单位为秒，最少60秒。</div>
                                </div>

                            </div>
                            <!--Feed 流-->
                            <div class="layui-tabs-item" lay-id="feed">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">偏好内容数量</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[recommend_prefer_limit]"
                                            value="{{ $settings['drama.feed_prefer_limit'] ?? '12' }}"
                                            placeholder="请输入偏好内容数量" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">热门数量</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[feed_hot_limit]"
                                            value="{{ $settings['drama.feed_hot_limit'] ?? '6' }}"
                                            placeholder="请输入热门数量" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">新剧数量</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[feed_new_limit]"
                                            value="{{ $settings['drama.feed_new_limit'] ?? '4' }}"
                                            placeholder="请输入新剧数量" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">新剧天数范围</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[feed_new_days]"
                                            value="{{ $settings['drama.feed_new_days'] ?? '7' }}"
                                            placeholder="请输入新剧天数范围" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">未登录热门数量</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[feed_guest_hot_limit]"
                                            value="{{ $settings['drama.feed_guest_hot_limit'] ?? '12' }}"
                                            placeholder="请输入未登录热门数量" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">未登录新剧数量</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="drama[feed_guest_new_limit]"
                                            value="{{ $settings['drama.feed_guest_new_limit'] ?? '8' }}"
                                            placeholder="请输入未登录新剧数量" class="layui-input">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                                lay-filter="save">提交</button>
                            <button type="reset" class="pear-btn pear-btn-md">
                                重置
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        //提交事件
        layui.use(["form", "popup"], function() {
            let form = layui.form;
            let $ = layui.$;
            let popup = layui.popup;
            form.on("submit(save)", function(data) {
                let loading = layer.load();
                $.ajax({
                    url: "{{ route('admin.system-config.store') }}",
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
        });
    </script>
@endpush
