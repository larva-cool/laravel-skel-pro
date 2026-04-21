<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 系统设置
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class StoreConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 基本信息
            'system.url' => ['required', 'url'],
            'system.m_url' => ['required', 'url'],
            'system.title' => ['required', 'string', 'max:255'],
            'system.keywords' => ['nullable', 'string', 'max:255'],
            'system.description' => ['nullable', 'string', 'max:255'],
            'system.icp_beian' => ['nullable', 'string', 'max:255'],
            'system.police_beian' => ['nullable', 'string', 'max:255'],
            'system.support_email' => ['nullable', 'email'],
            'system.lawyer_email' => ['nullable', 'email'],

            // 用户设置
            'user.enable_register' => ['required', 'boolean', 'in:0,1'],
            'user.enable_phone_register' => ['required', 'boolean', 'in:0,1'],
            'user.enable_email_register' => ['required', 'boolean', 'in:0,1'],
            'user.enable_wechat_login' => ['required', 'boolean', 'in:0,1'],
            'user.enable_apple_login' => ['required', 'boolean', 'in:0,1'],
            'user.enable_phone_login' => ['required', 'boolean', 'in:0,1'],
            'user.enable_password_login' => ['required', 'boolean', 'in:0,1'],
            'user.only_one_device_login' => ['required', 'boolean', 'in:0,1'],
            'user.register_throttle' => ['nullable', 'string', 'max:255'],
            'user.login_throttle' => ['nullable', 'string', 'max:255'],
            'user.username_change' => ['required', 'integer', 'min:0'],
            'user.token_expiration' => ['required', 'integer', 'min:0'],
            'user.point_expiration' => ['required', 'integer', 'min:0'],
            // 'user.invite_award_point' => ['required', 'integer', 'min:0'],
            // 'user.invite_award_contribution' => ['required', 'integer', 'min:0'],

            // 短信设置
            'sms.region_id' => ['nullable', 'string', 'max:255'],
            'sms.sms_account' => ['nullable', 'string', 'max:255'],
            'sms.sign_name' => ['nullable', 'string', 'max:255'],
            'sms.template_id' => ['nullable', 'string', 'max:255'],

            // 短信验证码
            'sms_captcha.duration' => ['required', 'integer', 'min:0'],
            'sms_captcha.length' => ['required', 'integer', 'min:0'],
            'sms_captcha.wait_time' => ['required', 'integer', 'min:0'],
            'sms_captcha.test_limit' => ['required', 'integer', 'min:0'],
            'sms_captcha.ip_count' => ['required', 'integer', 'min:0'],
            'sms_captcha.phone_count' => ['required', 'integer', 'min:0'],

            // 邮件验证码
            'email_captcha.duration' => ['required', 'integer', 'min:0'],
            'email_captcha.length' => ['required', 'integer', 'min:0'],
            'email_captcha.wait_time' => ['required', 'integer', 'min:0'],
            'email_captcha.test_limit' => ['required', 'integer', 'min:0'],

            // 上传设置
            'upload.storage' => ['required', 'string', 'max:255'],
            'upload.name_rule' => ['required', 'string', 'max:255'],
            'upload.allow_extension' => ['required', 'string', 'max:255'],
            'upload.allow_video_extension' => ['required', 'string', 'max:255'],

            // OpenAI 配置
            'openai.base_uri' => ['nullable', 'string', 'max:500'],
            'openai.organization' => ['nullable', 'string', 'max:255'],
            'openai.project' => ['nullable', 'string', 'max:255'],
            'openai.api_key' => ['nullable', 'string', 'max:255'],
            'openai.request_timeout' => ['nullable', 'integer', 'min:3'],
        ];
    }
}
