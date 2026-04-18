<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Models\User;
use App\Support\UserHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        UserHelper::createByPhone('14000000000')->updateQuietly(['name' => '测试号🐶']);
        UserHelper::createByPhone('14000000001')->updateQuietly(['name' => '测试号🐱']);
        UserHelper::createByPhone('14000000002')->updateQuietly(['name' => '测试号🐭']);
        UserHelper::createByPhone('14000000003')->updateQuietly(['name' => '测试号🐹']);
        UserHelper::createByPhone('14000000004')->updateQuietly(['name' => '测试号🐰']);
        UserHelper::createByPhone('14000000005')->updateQuietly(['name' => '测试号🐻']);
        UserHelper::createByPhone('14000000006')->updateQuietly(['name' => '测试号🐼']);
        UserHelper::createByPhone('14000000007')->updateQuietly(['name' => '测试号🐨']);
        UserHelper::createByPhone('14000000008')->updateQuietly(['name' => '测试号🐯']);
        UserHelper::createByPhone('14000000009')->updateQuietly(['name' => '测试号🦁']);
        UserHelper::createByPhone('14000000010')->updateQuietly(['name' => '测试号🐮']);
        UserHelper::createByPhone('14000000011')->updateQuietly(['name' => '测试号🐷']);
        UserHelper::createByPhone('14000000012')->updateQuietly(['name' => '测试号🐸']);
        UserHelper::createByPhone('14000000013')->updateQuietly(['name' => '测试号🐙']);
        UserHelper::createByPhone('14000000014')->updateQuietly(['name' => '测试号🐵']);
        UserHelper::createByPhone('14000000015')->updateQuietly(['name' => '测试号🐔']);
        UserHelper::createByPhone('14000000016')->updateQuietly(['name' => '测试号🦆']);
        UserHelper::createByPhone('14000000017')->updateQuietly(['name' => '测试号🐥']);
        UserHelper::createByPhone('14000000018')->updateQuietly(['name' => '测试号🦉']);
        UserHelper::createByPhone('14000000019')->updateQuietly(['name' => '测试号🐌']);
        UserHelper::createByPhone('14000000020')->updateQuietly(['name' => '测试号🐞']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $users = User::query()->with(['extra', 'profile'])->whereIn('phone', [
            '14000000000', '14000000001', '14000000002', '14000000003', '14000000004', '14000000005', '14000000006', '14000000007', '14000000008', '14000000009', '14000000010',
            '14000000011', '14000000012', '14000000013', '14000000014', '14000000015', '14000000016', '14000000017', '14000000018', '14000000019', '14000000020',
        ])->get();
        $users->each(function (User $user) {
            $user->delete();
        });
    }
};
