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
        Schema::dropIfExists('owners');
    }
}
