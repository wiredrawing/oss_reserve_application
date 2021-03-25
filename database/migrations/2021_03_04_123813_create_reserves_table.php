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
            // 予約対象
            $table->unsignedBigInteger("service_id");
            // 予約対象となるサービスの外部キー
            $table->foreign("service_id")->references("id")->on("services");
            // 使用開始予定日
            $table->dateTime("from_datetime");
            // 使用終了日
            $table->dateTime("to_datetime");
            // ゲストID
            $table->unsignedBigInteger("guest_id");
            // 予約申請者はguestテーブルに登録すること
            $table->foreign("guest_id")->references("id")->on("guests");
            $table->text("memo")->nullable();
            $table->tinyInteger("is_canceled")->default(0);
            $table->integer("created_by")->nullable();
            $table->integer("updated_by")->nullable();

            // 予約確定フラグ 0の場合は仮押さえ
            $table->tinyInteger("is_confirmed")->default(0);
            // 仮押さえの有効期限(初回POST後､30分)
            $table->dateTime("expired_at");

            // 仮押さえ中のユーザーを識別するためのトークン｡予約確定後はNULLに
            $table->string("user_token", 512)->nullable();

            $table->timestamps();

            // ユニークキー
            $table->unique("user_token");
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
