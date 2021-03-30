<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{


    protected $fillable = [
        "service_name",
        "service_type",
        "owner_id",
        "capacity",
        "price",
        "price_per_hour",
        "memo",
        "is_displayed",
        "is_deleted",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
    ];

    protected $appends = [
        "created_at_ja",
        "updated_at_ja",
    ];

    public function reserves()
    {
        return $this->hasMany(Reserve::class, "service_id");
    }

    public function owner()
    {
        return $this->hasOne(Owner::class, "id", "owner_id");
    }

    // レコード作成日時
    public function getCreatedAtJaAttribute()
    {
        return $this->created_at->format("Y年n月j日")."<br>".$this->created_at->format("H時i分:s秒");
    }

    // レコード更新日時
    public function getUpdatedAtJaAttribute()
    {
        return $this->updated_at->format("Y年n月j日")."<br>".$this->updated_at->format("H時i分:s秒");
    }
}
