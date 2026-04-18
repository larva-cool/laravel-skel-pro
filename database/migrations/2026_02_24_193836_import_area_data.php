<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use App\Models\System\Area;
use App\Support\FileHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! app()->runningUnitTests()) {// testing 跳过
            ini_set('memory_limit', '-1');
            $districts = FileHelper::json(database_path('data/areas-20260226.json'));
            DB::transaction(function () use ($districts) {
                Area::insert($districts);
            });
            unset($districts);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Area::truncate();
    }
};
