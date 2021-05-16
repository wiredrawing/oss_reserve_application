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
            // サービスの所有者
            $table->unsignedBigInteger("owner_id");
            // サービス所有者の外部キー
            $table->foreign("owner_id")->references("id")->on("owners");
            $table->text("memo")->nullable();
            // 利用可能人数
            $table->integer("capacity")->nullable();
            // 時間単価
            $table->integer("price_per_hour")->default(0);
            // そのサービスのタイプ 物品､部屋(ホテルなど)､人間などなど
            $table->integer("service_type")->default(0);
            // 一時的に予約不可にする場合
            $table->tinyInteger("is_displayed")->default(1);
            $table->tinyInteger("is_deleted")->default(0);
            // 当該サービスに関する商品説明(ウィジウィグエディタで編集)
            $table->text("service_contents")->nullable();
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamps();

            // ユニークキー
            $table->unique([
                // "owner_id",
                "service_name",
                "service_type",
            ]);
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
