<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{


    protected $fillable = [
        "login_id",
        "password",
        "email",
        "phone_number",
        "administrator_name",
        "last_login",
        "memo",
    ];
}
