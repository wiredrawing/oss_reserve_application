<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{


    protected $fillable = [
        "room_name",
        "owner_name",
        "capacity",
        "price",
        "is_displayed",
        "is_deleted",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
    ];
}
