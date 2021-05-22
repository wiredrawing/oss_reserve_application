<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            // 所有者名
            $table->string("owner_name", 512);
            // 所有者名(ふりがな)
            $table->string("owner_name_sort", 512);
            // オーナーのメールアドレス
            $table->string("email", 512)->nullable();
            // オーナーの電話番号
            $table->string("phone_number", 16);
            $table->text("description")->nullable();
            $table->text("memo")->nullable();
            $table->text("memo_for_admin")->nullable();
            // オーナー専用ページ表示用トークン
            $table->string("token", 512);
            // 当該オーナーを管理する管理者ID
            $table->unsignedBigInteger("administrator_id");
            $table->tinyInteger("is_displayed")->default(0);
            $table->tinyInteger("is_deleted")->default(0);
            $table->timestamps();

            $table->unique("token", "owners_token_unique");
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
        Schema::dropIfExists('owners');
    }
}
