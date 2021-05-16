<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservableTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservable_times', function (Blueprint $table) {
            $table->bigInteger("service_id");
            // 予約可能な日
            // 1 => "日曜日",
            // 2 => "月曜日",
            // 3 => "火曜日",
            // 4 => "水曜日",
            // 5 => "木曜日",
            // 6 => "金曜日",
            // 7 => "土曜日"
            $table->tinyInteger("reservable_day");
            // 予約可能開始時間
            $table->time("reservable_from");
            // 予約可能終了時間
            $table->time("reservable_to");
            // 予約可能日時に関しての注意事項
            $table->text("memo")->nullable();
            // 管理者側の注意事項
            $table->text("memo_for_admin")->nullable();
            $table->timestamps();

            // is_enabledと同等
            $table->tinyInteger("is_displayed")->default(Config("const.binary_type.on"));
            $table->tinyInteger("is_deleted")->default(Config("const.binary_type.off"));

            // primary
            $table->unique([
                "service_id",
                "reservable_day",
                "reservable_from",
                "reservable_to",
            ], "reservable_times_primary");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservable_times');
    }
}
