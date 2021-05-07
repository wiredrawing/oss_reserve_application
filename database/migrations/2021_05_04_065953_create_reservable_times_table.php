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
            $table->id();
            $table->bigInteger("service_id");
            // 予約可能な日
            // 0 => "日曜日",
            // 1 => "月曜日",
            // 2 => "火曜日",
            // 3 => "水曜日",
            // 4 => "木曜日",
            // 5 => "金曜日",
            // 6 => "土曜日"
            $table->tinyInteger("reservable_date");
            // 予約可能開始時間
            $table->time("reservable_from");
            // 予約可能終了時間
            $table->time("reservable_to");
            $table->timestamps();

            // is_enabledと同等
            $table->tinyInteger("is_displayed")->default(1);
            $table->tinyInteger("is_deleted")->default(0);

            $table->unique([
                "service_id",
                "reservable_date",
                "reservable_from",
                "reservable_to",
            ], "reservable_times_unique");
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
