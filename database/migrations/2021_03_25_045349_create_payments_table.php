<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // 支払い先の予約情報
            $table->unsignedBigInteger("reserve_id");
            // 一回の合計支払い金額
            $table->unsignedBigInteger("amount");
            $table->text("memo");
            $table->text("memo_for_admin");
            // 支払い確定日
            $table->dateTime("paid_at");
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
        Schema::dropIfExists('payments');
    }
}
