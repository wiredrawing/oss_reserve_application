<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            // 名前
            $table->string("family_name", 512);
            $table->string("given_name", 512);
            // 名前よみがな
            $table->string("family_name_sort", 512);
            $table->string("given_name_sort", 512);
            $table->string("email", 512);
            $table->string("password", 512);
            $table->string("phone_number", 512);
            // ゲスト専用のシークレットページ表示用トークン
            $table->string("token", 512);
            // ユーザー側の使う備考欄
            $table->text("memo")->nullable();
            // 管理側が使う備考欄
            $table->text("memo_for_admin")->nullable();
            $table->tinyInteger("is_displayed")->default(Config("const.binary_type.on"));
            $table->tinyInteger("is_deleted")->default(Config("const.binary_type.off"));
            // 電話問い合わによる登録の場合
            $table->integer("created_by")->nullable();
            // 情報更新者
            $table->integer("update_by")->nullable();
            $table->timestamps();

            $table->unique("token");
            $table->unique("email");

            // 論理削除
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
