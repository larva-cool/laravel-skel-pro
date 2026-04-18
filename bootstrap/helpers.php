<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Services\SettingManagerService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
use Laravel\Telescope\Telescope;
use Zhuzhichao\IpLocationZh\Ip;

/**
 * Get setting value or object.
 *
 * @param  mixed|null  $default
 * @return SettingManagerService|mixed
 */
if (! function_exists('settings')) {
    function settings(string $key = '', $default = null)
    {
        if (empty($key)) {
            return app(SettingManagerService::class);
        }

        return app(SettingManagerService::class)->get($key, $default);
    }
}

/**
 * get the morph map for polymorphic relations.
 *
 * @return array
 */
if (! function_exists('get_morph_maps')) {
    function get_morph_maps(): array
    {
        $maps = Relation::morphMap();
        foreach ($maps as $key => $val) {
            $maps[$key] = Lang::get('morph_maps.'.$val);
        }

        return $maps;
    }
}

if (! function_exists('cpu_count')) {
    /**
     * Get cpu count
     */
    function cpu_count(): int
    {
        // Windows does not support the number of processes setting.
        if (DIRECTORY_SEPARATOR === '\\') {
            return 1;
        }
        $count = 4;
        if (is_callable('shell_exec')) {
            if (strtolower(PHP_OS) === 'darwin') {
                $count = (int) shell_exec('sysctl -n machdep.cpu.core_count');
            } else {
                try {
                    $count = (int) shell_exec('nproc');
                } catch (Throwable $ex) {
                    // Do nothing
                }
            }
        }

        return $count > 0 ? $count : 4;
    }
}

/**
 * Create a new validation exception from a plain array of messages.
 */
if (! function_exists('validation_exception')) {
    function validation_exception($field, $message)
    {
        throw ValidationException::withMessages([
            $field => [$message],
        ]);
    }
}

/**
 * 限制值在指定范围内
 */
if (! function_exists('clamp')) {
    function clamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }
}

/**
 * 获取每页条数
 */
if (! function_exists('per_page')) {
    function per_page($request, int $limit = 15)
    {
        return clamp($request->input('per_page', $limit), 1, 100);
    }
}

/**
 * 手机号替换
 */
if (!function_exists('mobile_replace')) {
    function mobile_replace(?string $value, $character = '*', int $index = 3, int $length = 4): string
    {
        if (!$value) {
            return '';
        }

        return \Illuminate\Support\Str::mask($value, $character, $index, $length);
    }
}

/**
 * 解析UA
 */
if (! function_exists('parse_user_agent')) {
    function parse_user_agent($userAgent): array
    {
        $userAgent = trim($userAgent);
        $agent = new Agent;
        $agent->setUserAgent($userAgent);

        return [
            'platform' => $agent->platform(),
            'device' => $agent->device(),
            'browser' => $agent->browser(),
            'isMobile' => $agent->isMobile(),
            'isTablet' => $agent->isTablet(),
            'isDesktop' => $agent->isDesktop(),
            'isPhone' => $agent->isPhone(),
        ];
    }
}

/**
 * 解析IP归属地
 */
if (! function_exists('ip_address')) {
    function ip_address(string $ip)
    {
        $location = Ip::find($ip);

        return is_array($location) ? implode(' ', $location) : $location;
    }
}

/**
 * 安全处理字符串，移除特殊字符
 */
if (! function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        // 限制键长度
        if (strlen($key) > 255) {
            $key = substr($key, 0, 255);
        }

        return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
    }
}

/**
 * 解析被提及的用户名
 */
if (! function_exists('parse_mentioned_usernames')) {
    function parse_mentioned_usernames(string $content): array
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);

        return $matches[1] ?? [];
    }
}

/**
 * 代码中禁用 telescope，防止爆内存
 */
if (! function_exists('disable_telescope')) {
    function disable_telescope(): void
    {
        if (class_exists('Laravel\Telescope\Telescope')) {
            Telescope::stopRecording();
        }
    }
}
