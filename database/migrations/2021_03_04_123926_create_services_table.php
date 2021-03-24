<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 予約を提供するもの(サービステーブル)
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string("service_name", 512);
            // 物品の所有者
            $table->string("owner_id", 512)->default(0);
            $table->text("memo")->nullable();
            $table->integer("capacity")->nullable();
            // 枠ごとの時間
            $table->integer("price")->default(0);
            // 時間単価
            $table->integer("price_per_hour")->default(0);
            // そのサービスのタイプ 物品､部屋(ホテルなど)､人間などなど
            $table->integer("service_type")->default(0);
            // 一時的に予約不可にする場合
            $table->tinyInteger("is_displayed")->default(1);
            $table->tinyInteger("is_deleted")->default(0);
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamps();

            // // ユニークキー
            // $table->unique([
            //     "service_name",
            //     "service_type",
            // ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
