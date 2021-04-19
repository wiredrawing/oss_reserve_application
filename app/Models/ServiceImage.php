<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{


    protected $fillable = [
        "service_id",
        "image_id",
    ];


    /**
     * 紐づく画像一覧を取得する
     *
     * @return void
     */
    public function images()
    {
        return $this->hasMany(Image::class, "id", "image_id");
    }

}
