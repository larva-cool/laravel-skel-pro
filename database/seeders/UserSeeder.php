<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User\UserGroup;
use App\Support\UserHelper;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = UserHelper::create('support', '13800138000', 'support@email.com', '12345678');
        $user->markEmailAsVerified();
        $user->markPhoneAsVerified();
        $user->updateQuietly(['group_id' => UserGroup::query()->orderBy('id')->value('id')]);
    }
}
