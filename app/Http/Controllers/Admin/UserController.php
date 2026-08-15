<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Requests\Admin\User\UserAdjustBalanceRequest;
use App\Http\Requests\Admin\User\UserExtendVipRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\LoginHistoryResource;
use App\Http\Resources\Admin\UserDetailResource;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Password;

/**
 * 后台用户管理控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class UserController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        // $this->middleware('permission:users.index')->only(['index', 'show']);
        // $this->middleware('permission:users.edit')->only(['update', 'toggleStatus', 'resetPassword', 'resetContact', 'adjustBalance', 'extendVip']);
        // $this->middleware('permission:users.delete')->only(['destroy']);
    }

    /**
     * 用户列表（分页）
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = per_page($request);

        $query = User::query();

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (int) $request->query('status'));
        }

        if ($vip = $request->query('vip')) {
            if ($vip === '1') {
                $query->whereNotNull('vip_expires_at')->where('vip_expires_at', '>', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNull('vip_expires_at')->orWhere('vip_expires_at', '<=', now());
                });
            }
        }

        if ($startDate = $request->query('login_start')) {
            $query->whereDate('last_login_at', '>=', $startDate);
        }

        if ($endDate = $request->query('login_end')) {
            $query->whereDate('last_login_at', '<=', $endDate);
        }

        if ($registerStart = $request->query('register_start')) {
            $query->whereDate('created_at', '>=', $registerStart);
        }

        if ($registerEnd = $request->query('register_end')) {
            $query->whereDate('created_at', '<=', $registerEnd);
        }

        $items = $query->orderByDesc('id')->paginate($perPage);

        return UserResource::collection($items);
    }

    /**
     * 获取用户详情
     */
    public function show(User $user): UserDetailResource
    {
        return new UserDetailResource($user->load('profile'));
    }

    /**
     * 更新用户信息
     */
    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        $validated = $request->validated();
        $user->update($validated);

        return new UserResource($user->fresh());
    }

    /**
     * 删除用户（软删除）
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(status: 204);
    }

    /**
     * 启用/冻结用户
     */
    public function toggleStatus(User $user): UserResource
    {
        $user->status = $user->status->isActive() ? UserStatus::FROZEN : UserStatus::ACTIVE;
        $user->save();

        return new UserResource($user->fresh());
    }

    /**
     * 重置用户密码
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $user->resetPassword($data['password']);

        return response()->json(status: 204);
    }

    /**
     * 重置用户联系方式
     */
    public function resetContact(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:email,phone'],
            'value' => ['required', 'string'],
        ]);

        if ($data['type'] === 'email') {
            $request->validate([
                'value' => ['email', 'max:100', 'unique:users,email'],
            ]);
            $user->resetEmail($data['value']);
        } else {
            $request->validate([
                'value' => ['string', new \App\Rules\PhoneRule, 'unique:users,phone'],
            ]);
            $user->resetPhone($data['value']);
        }

        return response()->json(status: 204);
    }

    /**
     * 调整用户余额（积分/金币）
     */
    public function adjustBalance(UserAdjustBalanceRequest $request, User $user): UserResource
    {
        $data = $request->validated();
        $field = $data['type'] === 'points' ? 'available_points' : 'available_coins';
        $amount = (int) $data['amount'];

        $newValue = max(0, $user->{$field} + $amount);
        $user->{$field} = $newValue;
        $user->save();

        return new UserResource($user->fresh());
    }

    /**
     * 延长用户VIP
     */
    public function extendVip(UserExtendVipRequest $request, User $user): UserResource
    {
        $data = $request->validated();
        $user->addVipDays((int) $data['days']);

        return new UserResource($user->fresh());
    }

    /**
     * 获取用户登录历史（分页）
     */
    public function loginHistories(Request $request, User $user): AnonymousResourceCollection
    {
        $perPage = per_page($request);

        $query = $user->loginHistories();

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('ip', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhere('device', 'like', "%{$keyword}%");
            });
        }

        if ($startDate = $request->query('login_start')) {
            $query->whereDate('login_at', '>=', $startDate);
        }

        if ($endDate = $request->query('login_end')) {
            $query->whereDate('login_at', '<=', $endDate);
        }

        $items = $query->orderByDesc('login_at')->paginate($perPage);

        return LoginHistoryResource::collection($items);
    }
}
