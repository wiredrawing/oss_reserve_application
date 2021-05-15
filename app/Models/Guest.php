<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{


    protected $fillable = [
        "id",
        "family_name",
        "given_name",
        "family_name_sort",
        "given_name_sort",
        "email",
        "password",
        "phone_number",
        "token",
        "memo",
        "memo_for_admin",
        "is_displayed",
        "is_deleted",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
    ];


    protected $appends = [
        "guest_name",
        "guest_name_sort",
        "created_at_ja",
        "updated_at_ja",
    ];

    public function getGuestNameAttribute()
    {
        return $this->family_name . " " . $this->given_name;
    }

    public function getGuestNameSortAttribute()
    {
        return $this->family_name_sort . " " . $this->given_name_sort;
    }

    public function reserves ()
    {
        return $this->hasMany(Reserve::class, "guest_id");
    }

    // レコード作成日時日本語フォーマット化
    public function getCreatedAtJaAttribute()
    {
        return $this->created_at->format("Y年n月j日")."<br>".$this->created_at->format("H時i分s秒");
    }

    // レコード更新日時日本語フォーマット化
    public function getUpdatedAtJaAttribute()
    {
        return $this->updated_at->format("Y年n月j日")."<br>".$this->updated_at->format("H時i分s秒");
    }
}
