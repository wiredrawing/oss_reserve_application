<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->bigInteger("image_id");
            $table->bigInteger("guest_id");
            $table->timestamps();

            // primary
            $table->primary([
                "image_id",
                "guest_id",
            ]);

            // unique
            $table->unique([
                "image_id",
                "guest_id",
            ], "likes_image_id_guest_id_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
