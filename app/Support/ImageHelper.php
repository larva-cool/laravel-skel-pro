<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Typography\FontFactory;

/**
 * 图片助手(Intervention Image封装)
 *
 * @doc https://image.intervention.io/v3
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class ImageHelper
{
    /**
     * 检测是否是图片
     */
    public static function check(string $image): bool
    {
        return extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|webp|bmp|png)/i", $image, $m) && file_exists($image) && function_exists('imagecreatefrom'.($m[1] == 'jpg' ? 'jpeg' : $m[1]));
    }

    /**
     * 获取图片信息
     *
     * @return array|false
     */
    public static function info(string $img): bool|array
    {
        $imageInfo = getimagesize($img);
        if ($imageInfo === false) {
            return false;
        }
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
        $imageSize = filesize($img);

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageType,
            'size' => $imageSize,
            'mime' => $imageInfo['mime'],
        ];
    }

    /**
     * 压缩图片
     *
     * @param  string  $imagePath  图片文件路径
     * @param  int  $quality  压缩质量 (0-100, 默认80)
     * @param  string|null  $outputPath  输出路径，为null时覆盖原文件
     * @return string 压缩后的图片路径
     *
     * @throws \Exception 如果图片处理失败
     */
    public static function compress(string $imagePath, int $quality = 80, ?string $outputPath = null): string
    {
        // 检查文件是否存在
        if (! file_exists($imagePath)) {
            throw new \Exception("图片文件不存在: $imagePath");
        }

        // 验证质量参数
        if ($quality < 0 || $quality > 100) {
            throw new \Exception('压缩质量必须在0-100之间');
        }
        try {
            // 创建Intervention Image实例
            $image = Image::read($imagePath);

            // 确定输出路径
            $outputPath = $outputPath ?? $imagePath;

            // 保存压缩后的图片
            $image->save($outputPath, $quality);

            return $outputPath;
        } catch (\Exception $e) {
            throw new \Exception('图片压缩失败: '.$e->getMessage());
        }
    }

    /**
     * 等比缩放图片
     *
     * @param  string  $imagePath  图片文件路径
     * @param  int|null  $width  目标宽度 (与height至少提供一个，与ratio二选一)
     * @param  int|null  $height  目标高度 (与width至少提供一个，与ratio二选一)
     * @param  float|null  $ratio  缩放比例 (0-1，与width/height二选一)
     * @param  string|null  $outputPath  输出路径，为null时覆盖原文件
     * @return string 缩放后的图片路径
     *
     * @throws \Exception 如果图片处理失败
     */
    public static function resize(string $imagePath, ?int $width = null, ?int $height = null, ?float $ratio = null, ?string $outputPath = null): string
    {
        // 检查文件是否存在
        if (! file_exists($imagePath)) {
            throw new \Exception("图片文件不存在: $imagePath");
        }

        // 参数验证
        if (($width === null && $height === null) && $ratio === null) {
            throw new \Exception('必须提供宽度、高度或缩放比例中的至少一个');
        }

        if ($ratio !== null) {
            if ($ratio <= 0 || $ratio > 1) {
                throw new \Exception('缩放比例必须在0-1之间');
            }
            // 如果同时提供了比例和尺寸参数，优先使用比例
            $width = null;
            $height = null;
        } else {
            // 确保至少一个尺寸参数有效
            if ($width !== null && $width <= 0) {
                throw new \Exception('宽度必须大于0');
            }
            if ($height !== null && $height <= 0) {
                throw new \Exception('高度必须大于0');
            }
        }

        try {
            // 创建Intervention Image实例
            $image = Image::read($imagePath);

            // 获取原始尺寸
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // 计算新尺寸
            if ($ratio !== null) {
                // 使用缩放比例
                $newWidth = (int) ($originalWidth * $ratio);
                $newHeight = (int) ($originalHeight * $ratio);
            } else {
                // 使用指定尺寸，保持比例
                if ($width !== null && $height === null) {
                    $ratio = $width / $originalWidth;
                    $newWidth = $width;
                    $newHeight = (int) ($originalHeight * $ratio);
                } elseif ($height !== null && $width === null) {
                    $ratio = $height / $originalHeight;
                    $newHeight = $height;
                    $newWidth = (int) ($originalWidth * $ratio);
                } else {
                    // 同时提供了宽度和高度，使用较小的缩放比例以确保图片完全包含在指定尺寸内
                    $ratioWidth = $width / $originalWidth;
                    $ratioHeight = $height / $originalHeight;
                    $ratio = min($ratioWidth, $ratioHeight);
                    $newWidth = (int) ($originalWidth * $ratio);
                    $newHeight = (int) ($originalHeight * $ratio);
                }
            }

            // 调整图片大小
            $image->resize($newWidth, $newHeight);

            // 确定输出路径
            $outputPath = $outputPath ?? $imagePath;

            // 保存缩放后的图片
            $image->save($outputPath);

            return $outputPath;
        } catch (\Exception $e) {
            throw new \Exception('图片缩放失败: '.$e->getMessage());
        }
    }

    /**
     * 绘制图片
     *
     * @param  array  $config  配置
     * @param  string|null  $outputPath  输出路径，为null时使用系统临时目录
     * @return string 绘制后的图片路径
     *
     * @throws \Exception
     */
    public static function draw(array $config, ?string $outputPath = null): string
    {
        // 检查文件是否存在
        if (! file_exists($config['background'])) {
            throw new \Exception('图片文件不存在: '.$config['background']);
        }
        $image = Image::read($config['background']);
        if (! empty($config['places'])) {
            foreach ($config['places'] as $place) {
                $image->place($place['image'], $place['position'] ?? 'top-left', $place['x'] ?? 0, $place['y'] ?? 0, $place['opacity'] ?? 100);
            }
        }
        if (! empty($config['texts'])) {
            foreach ($config['texts'] as $text) {
                $image->text($text['content'], $text['x'] ?? 0, $text['y'] ?? 0, function (FontFactory $font) use ($text) {
                    if (! empty($text['font'])) {
                        $font->file($text['font']);
                    }
                    if (! empty($text['size'])) {
                        $font->size($text['size']);
                    }
                    if (! empty($text['color'])) {
                        $font->color($text['color']);
                    }
                    if (! empty($text['align'])) {
                        $font->align($text['align']);
                    }
                    if (! empty($text['valign'])) {
                        $font->valign($text['valign']);
                    }
                    if (! empty($text['line_height'])) {
                        $font->lineHeight($text['line_height']);
                    }
                });
            }
        }
        $fileExt = ! empty($config['encode']) ? $config['encode'] : 'jpg';
        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                // 编码为JPEG
                $image->encode(new JpegEncoder(quality: 100));
                break;
            case 'png':
                // 编码为PNG
                $image->encode(new PngEncoder);
                break;
            case 'gif':
                // 编码为GIF
                $image->encode(new GifEncoder);
                break;
            case 'webp':
            default:
                // 编码为WebP
                $image->encode(new WebpEncoder(quality: 100));
                break;
        }
        // 输出路径
        if (empty($outputPath)) {
            $outputPath = storage_path('framework/images');
        }
        FileHelper::makeDirectory($outputPath);
        $outputPath .= '/'.random_int(1000000, 9999999).'.'.$fileExt;

        $image->save($outputPath);

        return $outputPath;
    }
}
