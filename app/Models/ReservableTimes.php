<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservableTimes extends Model
{
    //


    protected $fillable = [
        "service_id",
        "reservable_day",
        "reservable_from",
        "reservable_to",
        "memo",
        "memo_for_admin",
        "is_displayed",
        "is_deleted",
    ];

    protected $appends = [
        "reservable_from_hour",
        "reservable_from_minute",
        "reservable_to_hour",
        "reservable_to_minute",
    ];

    // 紐づくサービス情報
    public function service()
    {
        return $this->belongsTo(Service::class, "service_id", "id");
    }


    // 予約開始時間
    // オプションプロパティ(時)
    public function getReservableFromHourAttribute()
    {
        $_time = explode(":", $this->reservable_from);
        return $_time[0];
    }

    // オプションプロパティ(分)
    public function getReservableFromMinuteAttribute()
    {
        $_time = explode(":", $this->reservable_from);
        return $_time[1];
    }

    // 予約終了時間
    public function getReservableToHourAttribute()
    {
        $_time = explode(":", $this->reservable_to);
        return $_time[0];
    }
    public function getReservableToMinuteAttribute()
    {
        $_time = explode(":", $this->reservable_to);
        return $_time[1];
    }
}
