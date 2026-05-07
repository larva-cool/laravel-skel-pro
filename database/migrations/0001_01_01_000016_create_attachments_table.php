<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('storage')->nullable()->comment('存储驱动');
            $table->string('origin_name')->comment('原始文件名');
            $table->string('file_name')->nullable()->comment('新文件名');
            $table->string('file_path')->comment('附件路径');
            $table->string('mime_type')->nullable()->comment('MIME类型');
            $table->unsignedInteger('file_size')->nullable()->comment('文件大小');
            $table->string('file_ext')->nullable()->comment('文件扩展名');
            $table->timestamps();

            $table->comment('附件表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
