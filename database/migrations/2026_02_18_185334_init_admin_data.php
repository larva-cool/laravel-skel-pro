<?php

use App\Enum\StatusSwitch;
use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AdminRole::create([
            'id' => 1,
            'name' => '超级管理员',
            'slug' => 'administrator',
        ]);
        Admin::create([
            'username' => 'admin',
            'phone' => '18615574213',
            'email' => 'xutongle@msn.com',
            'password' => Hash::make('password'),
            'status' => StatusSwitch::ENABLED->value,
        ]);
        Admin::query()->first()->roles()->save(AdminRole::query()->first());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
