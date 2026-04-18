<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

/**
 * Migration to create the 'areas' table in the database.
 *
 * This class defines the structure of the 'areas' table, which is used to store
 * geographical area information. It includes columns for area identification,
 * parent-child relationships, area names, codes, and ordering. The table also
 * supports soft deletes and timestamps for record management.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the 'areas' table with the specified columns and indexes.
     * Each column is commented to describe its purpose.
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id()->comment('区域编码');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父地区');
            $table->string('name', 50)->comment('地区名称');
            $table->unsignedInteger('area_code')->nullable()->comment('地区编码');
            $table->float('lat', 10, 6)->nullable()->comment('纬度');
            $table->float('lng', 10, 6)->nullable()->comment('经度');
            $table->string('city_code')->nullable()->comment('城市编码');
            $table->unsignedSmallInteger('order')->default(0)->nullable()->comment('排序');
            $table->timestamps();
            $table->softDeletes()->comment('删除时间');
            $table->index(['parent_id', 'deleted_at', 'order', 'id'], 'idx_parent_id');
            $table->index(['name', 'deleted_at'], 'idx_name');
            $table->index(['area_code', 'deleted_at'], 'idx_area_code');

            $table->comment('地区表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'areas' table if it exists, effectively undoing the changes made
     * by the `up` method.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
