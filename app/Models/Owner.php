<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{


    protected $fillable = [
        "owner_name",
        "owner_name_sort",
        "email",
        "phone_number",
        "description",
        "token",
        // 管理画面側メモ
        "memo",
        "memo_for_admin",
        "administrator_id",
    ];

    public function owner_images ()
    {
        return $this->hasMany(OwnerImage::class, "owner_id", "id");
    }
}
