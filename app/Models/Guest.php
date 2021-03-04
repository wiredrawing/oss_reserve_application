<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{


    protected $fillable = [
        "family_name",
        "given_name",
        "family_name_sort",
        "given_name_sort",
        "email",
        "phone_number",
        "token",
        "option",
        "memo",
        "is_displayed",
        "is_deleted",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
    ];
}
