<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Models\User\Nickname;
use App\Support\FileHelper;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! app()->runningUnitTests()) {// testing 跳过
            ini_set('memory_limit', '-1');
            $items = FileHelper::json(database_path('data/nickname-20251129.json'));
            $nicknames = [];
            foreach ($items as $key => $val) {
                $nicknames[] = ['nickname' => $val];
                // 5000个一组写入数据库
                if ($key % 5000 === 0) {
                    Nickname::insert($nicknames);
                    $nicknames = [];
                }
            }
            Nickname::insert($nicknames);
            unset($items, $nicknames);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Nickname::truncate();
    }
};
