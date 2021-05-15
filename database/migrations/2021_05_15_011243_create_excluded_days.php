<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcludedDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excluded_dates', function (Blueprint $table) {
            $table->id();
            // 対象のサービスID
            $table->bigInteger("service_id");
            // 更新者IDを指定
            $table->bigInteger("updated_by");
            // 臨時的に予約ができない状態にする日時
            // from~とto~で指定された日時は､サービスに予約可能日時として設定されていても予約不可
            $table->dateTime("from_excluded_at");
            $table->dateTime("to_excluded_at");
            // 予約不可能な理由など
            $table->text("memo")->nullable();
            $table->text("memo_for_admin")->nullable();
            // 予約除外日作成者
            $table->unsignedBigInteger("administrator_id");
            $table->timestamps();

            $table->unique([
                "service_id",
                "from_excluded_at",
                "to_excluded_at",
            ], "service_id_from_excluded_at_to_excluded_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excluded_dates');
    }
}
