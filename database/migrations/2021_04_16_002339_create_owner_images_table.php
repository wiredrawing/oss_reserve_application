<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnerImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owner_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("owner_id");
            $table->bigInteger("image_id");
            $table->timestamps();

            // unique
            $table->unique([
                "owner_id",
                "image_id",
            ], "owner_images_owner_id_image_id_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('owner_images');
    }
}
