<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Services;

use App\Enum\CacheKey;
use App\Enum\SettingType;
use App\Models\System\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * 系统配置管理服务
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SettingManagerService
{
    protected Collection $settings;

    /**
     * Create a new Setting instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->settings = new Collection;
    }

    /**
     * 加载所有
     *
     * @param  bool  $reload  强制重载
     */
    public function all(bool $reload = false): Collection
    {
        if ($this->settings->isNotEmpty() && ! $reload) {
            return $this->settings;
        }
        if (($settings = Cache::get(CacheKey::SETTINGS)) == null || $reload) {
            $settings = $this->getAllFromDatabase();
            Cache::put(CacheKey::SETTINGS, $settings, Carbon::now()->addHours(2));
        }
        $this->settings = collect($settings);

        return $this->settings;
    }

    /**
     * 获取配置值
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null)
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * 判断配置项是否存在
     */
    public function has(string $key): bool
    {
        return Arr::has($this->all(), $key);
    }

    /**
     * 获取配置组
     */
    public function tag(string $tag = 'default'): array
    {
        return Arr::get($this->all(), $tag);
    }

    /**
     * 保存设置
     *
     * @param  mixed|null  $value
     */
    public function set(string $key, $value = null, string $cast_type = 'string'): bool
    {
        if (is_array($value)) {
            return false;
        }
        // 写库
        $query = Setting::query()->where('key', '=', $key);
        $method = $query->exists() ? 'update' : 'insert';
        $query->$method(compact('key', 'value', 'cast_type'));
        $this->all(true);

        return true;
    }

    /**
     * 删除配置
     */
    public function forge(string $key): bool
    {
        Setting::query()->where('key', '=', $key)->delete();
        $this->all(true);

        return true;
    }

    /**
     * 从数据库加载所有配置
     */
    protected function getAllFromDatabase(): array
    {
        $settings = [];
        Setting::all()->each(function ($setting) use (&$settings) {
            $value = match ($setting['cast_type']) {
                SettingType::CAST_TYPE_INT, 'integer' => (int) $setting['value'],
                SettingType::CAST_TYPE_FLOAT => (float) $setting['value'],
                'boolean', SettingType::CAST_TYPE_BOOL => (bool) $setting['value'],
                default => $setting['value'],
            };
            $settings[$setting['key']] = $value;
            Arr::set($settings, $setting['key'], $value);
        });

        return $settings;
    }

    /**
     * 获取配置项类型
     */
    public function castTypes(): array
    {
        $castTypes = [];
        Setting::all()->each(function ($setting) use (&$settings, &$castTypes) {
            $castTypes[$setting['key']] = $setting['cast_type'];
        });

        return $castTypes;
    }
}
