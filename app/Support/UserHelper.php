<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Support;

use App\Enum\SocialProvider;
use App\Enum\UserStatus;
use App\Events\User\ModifyAvatar;
use App\Models\User;
use App\Models\User\Nickname;
use App\Models\User\UserExtra;
use App\Models\User\UserSocial;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 用户助手
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserHelper
{
    /**
     * 快速创建用户
     *
     * @param  string|null  $username  用户名
     * @param  string|int|null  $phone  手机号
     * @param  string|null  $email  邮箱
     * @param  string|null  $password  密码
     */
    public static function create(?string $username = null, string|int|null $phone = null, ?string $email = null, ?string $password = null): User
    {
        $username = $username ? self::generateUsername($username) : null;

        return User::create([
            'username' => $username ?? null,
            'phone' => $phone ?? null,
            'email' => $email ?? null,
            'name' => Nickname::getRandomNickname(),
            'password' => $password ?? null,
            'status' => UserStatus::STATUS_ACTIVE->value,
        ]);
    }

    /**
     * 通过手机创建用户
     *
     * @param  int|string  $phone  手机号
     * @param  string|null  $password  密码
     */
    public static function createByPhone(int|string $phone, ?string $password = null): User
    {
        $user = self::create(null, $phone, null, $password);
        $user->markPhoneAsVerified();

        return $user;
    }

    /**
     * 通过邮箱创建用户
     *
     * @param  string  $email  邮箱
     * @param  string|null  $password  密码
     */
    public static function createByEmail(string $email, ?string $password = null): User
    {
        return self::create(null, null, $email, $password);
    }

    /**
     * 通过用户名和邮箱创建用户
     *
     * @param  string  $username  用户名
     * @param  string  $email  邮箱
     * @param  string|null  $password  密码
     */
    public static function createByUsernameAndEmail(string $username, string $email, ?string $password = null): User
    {
        return static::create($username, null, $email, $password);
    }

    /**
     * 通过昵称创建用户
     *
     * @param  string  $name  昵称
     * @param  string|null  $password  密码
     */
    public static function createByName(string $name, ?string $password = null): User
    {
        $username = self::generateUsername($name);
        $user = self::create($username, null, $password);
        $user->updateQuietly(['name' => $name]);

        return $user;
    }

    /**
     * 查找手机用户，如果没有则根据系统规则创建
     */
    public static function findOrCreatePhone(int|string $phone, ?string $regSource = null): ?User
    {
        /** @var User $user */
        $user = User::withTrashed()->where('phone', $phone)->first();
        if (! $user) {
            $user = self::createByPhone($phone);
            if ($regSource) {
                $user->markRegSource($regSource);
            }
            Event::dispatch(new Registered($user));
        } elseif ($user->trashed()) {
            return null;
        }

        return $user;
    }

    /**
     * 通过邀请码获取用户ID
     */
    public static function findByInviteCode($inviteCode): ?int
    {
        $userId = UserExtra::query()->where('invite_code', $inviteCode)->value('user_id');

        return $userId ? (int) $userId : null;
    }

    /**
     * 随机生成一个用户名
     *
     * @param  string  $username  用户名
     */
    public static function generateUsername(string $username): string
    {
        if (User::withTrashed()->where('username', '=', $username)->exists()) {
            $row = User::withTrashed()->where('username', '=', $username)->count();
            $username = self::generateUsername($username.++$row);
        }

        return $username;
    }

    /**
     * 通过账号查找用户
     */
    public static function findForAccount(string $account): ?User
    {
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return User::query()->whereNotNull('email')->where('email', $account)->first();
        } elseif (preg_match('/^1[2-9]\d{9}$/', $account)) {
            return User::query()->whereNotNull('phone')->where('phone', $account)->first();
        } else {
            return User::query()->whereNotNull('username')->where('username', $account)->first();
        }
    }

    /**
     * 根据用户ID查找用户
     *
     * @param  int|string  $userId  用户ID
     * @param  bool  $lock  是否加锁
     *
     * @throws ModelNotFoundException<User>
     */
    public static function findById(int|string $userId, bool $lock = false): User
    {
        $query = User::query()->where('id', $userId);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    /**
     * 根据渠道和开放平台ID查找用户
     */
    public static function findByOpenid(SocialProvider $provider, string $openid): ?User
    {
        $socialUser = UserSocial::query()->with('user')->where('provider', $provider->value)->where('openid',
            $openid)->first();

        return $socialUser?->user;
    }

    /**
     * 根据渠道和开放平台UnionID查找用户
     */
    public static function findByUnionid(SocialProvider $provider, string $unionid): ?User
    {
        $socialUser = UserSocial::query()->with('user')->where('provider', $provider->value)->where('unionid',
            $unionid)->first();

        return $socialUser?->user;
    }

    /**
     * 连接邀请码
     *
     * @param  User  $user  用户
     * @param  string  $inviteCode  邀请码
     */
    public static function connectInvite(User $user, string $inviteCode): void
    {
        // 查询推荐人的User ID
        $inviterId = self::findByInviteCode($inviteCode);
        if ($inviterId) {
            $conn = UserExtra::query()->getConnection();
            // 开始事务
            $conn->beginTransaction();
            try {
                // 更新推荐人用户的邀请注册数
                UserExtra::query()->where('user_id', $inviterId)->increment('invite_registered_count');
                // 更新当前用户的推荐人ID
                UserExtra::query()->where('user_id', $user->id)->update(['referrer_id' => $inviterId]);
                $conn->commit();
            } catch (\Exception $e) {
                $conn->rollBack();
                Log::error('关联邀请码失败', ['user' => $user, 'inviteCode' => $inviteCode, 'exception' => $e]);
                throw $e;
            }
        }
    }

    /**
     * 通过用户名获取用户ID
     */
    public static function getIdByName(string $name): ?int
    {
        return User::query()->where('username', $name)->select(['id'])->value('id');
    }

    /**
     * 解析被提及的用户ID
     */
    public static function parseMentionedIds(string $content): array
    {
        $mentionedUsernames = parse_mentioned_usernames($content);
        $mentionedUserIds = [];
        foreach ($mentionedUsernames as $mentionedUsername) {
            $mentionedUserIds[] = UserHelper::getIdByName($mentionedUsername);
        }

        return $mentionedUserIds;
    }

    /**
     * 获取头像 Url
     */
    public static function getAvatar(?string $avatar): string
    {
        if (! empty($avatar)) {
            if (URL::isValidUrl($avatar)) {
                return $avatar;
            }

            return Storage::disk()->url($avatar);
        }

        return asset(User::DEFAULT_AVATAR);
    }

    /**
     * 设置用户头像
     *
     * @param  UploadedFile|File  $file
     */
    public static function setAvatar(User $user, $file): string
    {
        // 销毁原始头像
        $user->resetAvatar();
        $filepath = Storage::disk()->putFileAs(
            FileHelper::generateDirectoryPath($user->id, 'avatar'),
            $file,
            $user->id.'.'.$file->extension()
        );
        if ($filepath) {
            $user->updateQuietly(['avatar' => $filepath]);
            Event::dispatch(new ModifyAvatar($user));

            return $filepath;
        }

        return '';
    }
}
