<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

/**
 * 文件助手，仅用于文件操作，不建议直接使用文件系统类
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class FileHelper
{
    /**
     * 读取文件内容
     */
    public static function get(string $path): false|string
    {
        // 检查文件是否存在
        if (! file_exists($path)) {
            return false;
        }

        return @file_get_contents($path);
    }

    /**
     * 写入文件
     */
    public static function write(string $path, string $content): false|string
    {
        // 检查目录是否存在
        if (! self::makeDirectory(dirname($path))) {
            return false;
        }

        $status = file_put_contents($path, $content);

        return $status ? $path : false;
    }

    /**
     * 写入数组到JSON文件
     */
    public static function writeJson(string $path, array $data, int $flags = 0): false|string
    {
        $content = json_encode($data, $flags);

        return self::write($path, $content);
    }

    /**
     * 读取JSON文件到数组
     */
    public static function json(string $path, int $flags = 0): array
    {
        $content = self::get($path);

        if (! $content) {
            return [];
        }

        $data = json_decode($content, true, 512, $flags);

        return is_array($data) ? $data : [];
    }

    /**
     * 确保目录存在
     */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        $realPath = realpath($path);
        if ($realPath && is_dir($realPath)) {
            return true;
        }
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * 生成目录路径
     *
     * @param  string  $directory  目录名
     */
    public static function generateDirectoryPath(float|int|string $id, string $directory = 'avatar'): string
    {
        $id = sprintf('%09d', $id);
        $dir1 = substr($id, 0, 3);
        $dir2 = substr($id, 3, 4);

        return $directory.'/'.$dir1.'/'.$dir2;
    }
}
