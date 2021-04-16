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
            $table->text("description")->nullable();
            $table->tinyInteger("is_displayed")->default(0);
            $table->tinyInteger("is_deleted")->default(0);
            $table->string("filename", 512);
            $table->string("token", 512);
            // 画像に対していいねできる
            $table->bigInteger("good_count")->default(0);
            $table->timestamps();
            $table->unique("token", "image_token_unique");
            $table->unique("filename", "image_filename_unique");
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
