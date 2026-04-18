<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Services;

use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * 上传服务
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UploadService
{
    /**
     * Use (unique or datetime or sequence) name for store upload file.
     */
    protected string $generateName = '';

    /**
     * 存储驱动名称
     */
    protected string $storageName = '';

    /**
     * 文件系统
     */
    protected Filesystem $filesystem;

    /**
     * UploadService constructor.
     */
    public function __construct()
    {
        $this->generateName = settings('upload.name_rule', '');
        $this->storageName = settings('upload.storage', '');
        $this->filesystem = Storage::disk($this->storageName);
    }

    /**
     * 获取文件服务实例
     */
    public static function getInstance(): UploadService
    {
        return app(UploadService::class);
    }

    /**
     * 获取文件系统路径
     */
    public function path(string $path): string
    {
        return $this->filesystem->path($path);
    }

    /**
     * Get file visit url.
     */
    public function url(string $path): string
    {
        if (URL::isValidUrl($path)) {
            return $path;
        }

        return $this->filesystem->url($path);
    }

    /**
     * 从文件URL中获取相对路径
     */
    public function relativePath(string $path): string
    {
        if (! empty($path)) {
            $config = $this->filesystem->getConfig();
            if (! empty($config['url'])) {
                return str_replace($config['url'], '', $path);
            }

            return parse_url($path, PHP_URL_PATH);
        }

        return $path;
    }

    /**
     * 获取临时下载地址
     *
     * @param  DateTimeInterface  $expiration  链接有效期
     */
    public function temporaryUrl(string $file, DateTimeInterface $expiration): string
    {
        return $this->filesystem->temporaryUrl($file, $expiration);
    }

    /**
     * 获取临时上传地址
     *
     * @param  string  $path  上传路径
     * @param  DateTimeInterface  $expiration  链接有效期
     * @param  array  $options  上传选项
     */
    public function temporaryUploadUrl(string $path, DateTimeInterface $expiration, array $options = []): array
    {
        return $this->filesystem->temporaryUploadUrl($path, $expiration, $options);
    }

    /**
     * 销毁原始文件
     *
     * @return void.
     */
    public function destroy(string $path): void
    {
        if (URL::isValidUrl($path)) {
            $path = parse_url($path, PHP_URL_PATH);
        }
        if (! empty($path) && $this->filesystem->exists($path)) {
            $this->filesystem->delete($path);
        }
    }

    /**
     * Upload file.
     */
    public function uploadFile(UploadedFile $file): false|array
    {
        $fileName = $this->generateName($file);
        if ($this->filesystem->exists("{$this->getDirectory()}/$fileName")) {
            $fileName = $this->generateUniqueName($file);
        }
        $filePath = $this->filesystem->putFileAs($this->getDirectory(), $file, $fileName);
        if ($filePath) {
            return [
                'storage' => $this->storageName,
                'origin_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_ext' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getClientMimeType(),
            ];
        }

        return false;
    }

    /**
     * 检查文件是否是图片
     */
    private function getImageInfo($filePath): bool
    {
        [$width, $height, $type, $attr] = getimagesize($filePath);

        return $type !== false;
    }

    /**
     * 获取文件存储目录
     */
    private function getDirectory(): string
    {
        return 'uploads/'.date('Y/m/d');
    }

    /**
     * 获取文件存储名称
     */
    private function generateName(UploadedFile $file): string
    {
        if ($this->generateName == 'unique') {
            return $this->generateUniqueName($file);
        } elseif ($this->generateName == 'datetime') {
            return $this->generateDatetimeName($file);
        } elseif ($this->generateName == 'sequence') {
            return $this->generateSequenceName($file);
        }

        return $this->generateClientName($file);
    }

    /**
     * Generate a unique name for uploaded file.
     */
    private function generateUniqueName(UploadedFile $file): string
    {
        return md5(uniqid().microtime()).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Generate a datetime name for uploaded file.
     */
    private function generateDatetimeName(UploadedFile $file): string
    {
        return date('YmdHis').mt_rand(10000, 99999).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Generate a sequence name for uploaded file.
     */
    private function generateSequenceName(UploadedFile $file): string
    {
        $index = 1;
        $extension = $file->getClientOriginalExtension();
        $original = $file->getClientOriginalName();
        $new = sprintf('%s_%s.%s', $original, $index, $extension);
        while ($this->filesystem->exists("{$this->getDirectory()}/$new")) {
            $index++;
            $new = sprintf('%s_%s.%s', $original, $index, $extension);
        }

        return $new;
    }

    /**
     * Use file'oldname for uploaded file.
     */
    private function generateClientName(UploadedFile $file): string
    {
        return $file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
    }
}
