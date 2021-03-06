<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{



    protected $fillable = [
        "from_datetime",
        "to_datetime",
        "room_id",
        "guest_id",
        "memo",
        "is_canceled",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
        // 仮予約期間
        "expired_at",
    ];
}
