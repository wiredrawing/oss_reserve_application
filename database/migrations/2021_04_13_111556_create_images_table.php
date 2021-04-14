<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string("name", 256);
            $table->text("description");
            $table->bigInteger("service_id")->nullable();
            $table->bigInteger("owner_id")->nullable();
            $table->tinyInteger("is_displayed")->default(0);
            $table->tinyInteger("is_deleted")->default(0);
            $table->string("token", 512);
            // 画像に対していいねできる
            $table->bigInteger("good_count")->default(0);
            $table->timestamps();
            $table->unique("token", "image_token_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
