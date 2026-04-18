<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin\AdminMenu;
use App\Models\User;
use App\Models\User\UserStat;
use App\Support\FileHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 后台首页
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class IndexController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display home page.
     */
    public function index()
    {
        return view('admin.index.index');
    }

    /**
     * Display dashboard page.
     */
    public function dashboard()
    {
        // 今日新增用户数
        $todayUserCount = UserStat::getTodayRegistration();
        // 7天内新增用户数
        $day7UserCount = UserStat::getRecentDaysRegistration(7);
        // 30天内新增用户数
        $day30UserCount = UserStat::getRecentDaysRegistration(30);
        // 总用户数
        $userCount = User::query()->count();
        // mysql版本
        $mysqlVersion = 'unknown';
        try {
            $connection = DB::connection();
            if ($connection->getDriverName() === 'mysql') {
                $version = DB::select('select VERSION() as version');
                $mysqlVersion = $version[0]->version ?? 'unknown';
            }
        } catch (\Exception $e) {

        }

        $day7Detail = [];
        $now = time();
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', $now - 24 * 60 * 60 * $i);
            $day7Detail[substr($date, 5)] = User::query()
                ->where('created_at', '>', "$date 00:00:00")
                ->where('created_at', '<', "$date 23:59:59")
                ->count();
        }

        return view('admin.index.dashboard', [
            'today_user_count' => $todayUserCount,
            'day7_user_count' => $day7UserCount,
            'day30_user_count' => $day30UserCount,
            'user_count' => $userCount,
            'laravel_version' => app()->version(),
            'laravel_environment' => app()->environment(),
            'mysql_version' => $mysqlVersion,
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'day7_detail' => array_reverse($day7Detail),
        ]);
    }

    /**
     * Get config.
     */
    public function config(): JsonResponse
    {
        $dashboard = AdminMenu::query()
            ->select(['id', 'href', 'title'])->where('key', 'admin.index.dashboard')
            ->first();
        $config = FileHelper::json(public_path('admin/config.json'));
        $config['logo']['title'] = config('app.name', 'Laravel');
        $config['menu']['data'] = route('admin.menus.left_menu');
        $config['menu']['select'] = $dashboard['id'];
        $config['tab']['index'] = $dashboard;

        return response()->json($config);
    }

    /**
     * 管理员资料
     */
    public function account(): AdminResource
    {
        $user = Auth::guard('admin')->user();

        return new AdminResource($user);
    }
}
