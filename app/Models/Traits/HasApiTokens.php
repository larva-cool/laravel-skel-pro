<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\NewAccessToken;

trait HasApiTokens
{
    use \Laravel\Sanctum\HasApiTokens {
        \Laravel\Sanctum\HasApiTokens::createToken as createBaseToken;
    }

    /**
     * 删除指定设备所有
     */
    public function flushTokenByName(string $name): bool
    {
        return (bool) $this->tokens()->where('name', $name)->delete();
    }

    /**
     * 删除所有
     */
    public function flushTokens(): bool
    {
        return (bool) $this->tokens()->delete();
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name  名称
     * @param  array  $abilities  权限列表
     */
    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        // 如果开启了不允许多设备登录，这里先删除所有 token
        if (settings('user.only_one_device_login', false)) {
            $this->flushTokenByName($name);
        }
        if (! $expiresAt) {
            $expiresAt = Carbon::now()->addMinutes((int) settings('user.token_expiration', 525600));
        }

        return $this->createBaseToken($name, $abilities, $expiresAt);
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name  名称
     * @param  array  $abilities  权限列表
     * @return array 包含 token 信息的数组，键有 'token_id'、'token_type'、'access_token'、'expires_in'
     */
    public function createDeviceToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): array
    {
        $token = $this->createToken($name, $abilities, $expiresAt);

        return [
            'token_id' => $token->accessToken->id,
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_in' => (int) $token->accessToken->expires_at?->diffInSeconds(Carbon::now(), true),
        ];
    }
}
