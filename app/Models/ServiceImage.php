<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{

    use \LaravelTreats\Model\Traits\HasCompositePrimaryKey;

    protected $primaryKey = [
        "service_id",
        "image_id",
    ];

    protected $fillable = [
        "service_id",
        "image_id",
    ];

    public $incrementing = false;


    /**
     * 紐づく画像一覧を取得する
     *
     * @return void
     */
    public function image()
    {
        return $this->hasOne(Image::class, "id", "image_id");
    }

    /**
     * 紐づくサービス情報を取得する
     *
     * @return void
     */
    public function service()
    {
        return $this->belongsTo(Service::class, "service_id", "id");
    }
}
