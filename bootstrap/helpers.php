<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;

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
if (! function_exists('mobile_replace')) {
    function mobile_replace(?string $value, $character = '*', int $index = 3, int $length = 4): string
    {
        if (! $value) {
            return '';
        }

        return Str::mask($value, $character, $index, $length);
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
 * 禁用 telescope，防止爆内存
 */
if (! function_exists('disable_telescope')) {
    function disable_telescope(): void
    {
        if (class_exists('Laravel\Telescope\Telescope')) {
            Telescope::stopRecording();
        }
    }
}
