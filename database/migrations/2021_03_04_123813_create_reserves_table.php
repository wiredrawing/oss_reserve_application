<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            // 使用開始予定日
            $table->dateTime("from_datetime");
            // 使用終了日
            $table->dateTime("to_datetime");
            $table->bigInteger("room_id");
            // ゲストID
            $table->bigInteger("guest_id");
            $table->text("memo")->nullable();
            $table->tinyInteger("is_canceled")->default(0);
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserves');
    }
}
