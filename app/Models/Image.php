<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $fillable = [
        "name",
        "description",
        "token",
        "is_displayed",
        "is_deleted",
        "filename",
        "good_count",
    ];

    protected $appends = [
        "show_url",
    ];


    public function getShowUrlAttribute()
    {
        return action("Api\\v1\\Front\\ImageController@show", [
            "image_id" => $this->id
        ]);
    }
}
