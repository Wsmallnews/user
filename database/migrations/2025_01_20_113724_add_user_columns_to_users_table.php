<?php

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
        Schema::whenTableDoesntHaveColumn(
            'users',
            'username',
            function (Blueprint $table) {
                $table->string('username')->nullable()->unique()->after('name')->comment('用户名');
            }
        );
        Schema::whenTableDoesntHaveColumn(
            'users',
            'mobile',
            function (Blueprint $table) {
                $table->string('mobile', 20)->nullable()->unique()->after('username')->comment('手机号');
            }
        );
        Schema::whenTableDoesntHaveColumn(
            'users',
            'avatar_url',
            function (Blueprint $table) {
                $table->string('avatar_url')->nullable()->after('mobile')->comment('头像');
            }
        );
        Schema::whenTableDoesntHaveColumn(
            'users',
            'gender',
            function (Blueprint $table) {
                $table->string('gender', 20)->default('undisclosed')->after('avatar_url')->comment('性别');
            }
        );
        Schema::whenTableDoesntHaveColumn(
            'users',
            'birthday',
            function (Blueprint $table) {
                $table->string('birthday', 20)->nullable()->after('gender')->comment('生日');
            }
        );
        Schema::whenTableDoesntHaveColumn(
            'users',
            'status',
            function (Blueprint $table) {
                $table->string('status', 20)->default('normal')->after('remember_token')->comment('状态:normal=正常,disabled=禁用');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不删字段
    }
};
