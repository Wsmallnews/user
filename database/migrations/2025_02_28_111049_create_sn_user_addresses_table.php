<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sn_user_addresses', function (Blueprint $table) {
            $table->comment('收货地址');
            $table->engine = 'InnoDB';
            $table->id();
            $table->morphs('user');
            $table->string('consignee')->nullable()->comment('收货人');
            $table->tinyInteger('gender')->default(0)->comment('性别');
            $table->string('mobile', 30)->nullable()->comment('收货手机');
            $table->string('province_name', 30)->nullable()->comment('省份');
            $table->string('city_name', 30)->nullable()->comment('城市');
            $table->string('district_name', 30)->nullable()->comment('地区');
            $table->string('address')->nullable()->comment('详细地址');
            $table->string('street_number')->nullable()->comment('门牌号');
            $table->unsignedInteger('province_id')->default(0)->comment('省Id');
            $table->unsignedInteger('city_id')->default(0)->comment('市Id');
            $table->unsignedInteger('district_id')->default(0)->comment('区Id');
            $table->decimal('longitude', 10, 6)->default(0)->comment('经度');
            $table->decimal('latitude', 10, 6)->default(0)->comment('纬度');
            $table->unsignedInteger('used_num')->default(0)->comment('使用次数');
            $table->tinyInteger('is_default')->default(0)->comment('是否默认');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sn_user_addresses');
    }
};
