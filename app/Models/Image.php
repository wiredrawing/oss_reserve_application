<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $fillable = [
        "name",
        "description",
        // service_id あるいは owner_idをマストとする
        "service_id",
        "owner_id",
        "token",
        "is_displayed",
        "is_deleted",
    ];
}
