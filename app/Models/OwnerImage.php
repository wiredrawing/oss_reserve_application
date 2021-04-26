<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerImage extends Model
{

    use \LaravelTreats\Model\Traits\HasCompositePrimaryKey;

    protected $primaryKey = [
        "owner_id",
        "image_id",
    ];

    protected $fillable = [
        "owner_id",
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
     * 紐づくオーナー情報を取得する
     *
     * @return void
     */
    public function owner()
    {
        return $this->belongsTo(Owner::class, "owner_id", "id");
    }
}
