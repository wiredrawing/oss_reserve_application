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
    ];
}
