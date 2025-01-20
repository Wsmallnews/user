<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Fortify;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name')->comment('用户名');
            $table->string('mobile', 20)->nullable()->unique()->after('username')->comment('手机号');
            $table->tinyInteger('gender')->default(0)->after('avatar')->comment('性别:1=男,2=女,0=未知');
            $table->string('birthday')->after('gender')->comment('生日');
            $table->string('status', 20)->default('normal')->after('remember_token')->comment('状态:normal=正常,disabled=禁用');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'mobile',
                'gender',
                'birthday',
                'status',
            ]);
        });
    }
};
