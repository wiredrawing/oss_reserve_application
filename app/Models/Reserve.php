<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{



    protected $fillable = [
        "from_datetime",
        "to_datetime",
        "service_id",
        "guest_id",
        "memo",
        "is_canceled",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
        // 仮予約期間
        "expired_at",
        // ユーザーの編集画面表示用
        "user_token",
    ];


    public function service()
    {
        return $this->belongsTo(Service::class, "service_id");
    }
}
