<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImageRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $method = strtoupper($this->getMethod());
        $route_name = Route::currentRouteName();

        if ($method === "POST") {

            if ($route_name === "api.front.image.upload") {
                $rules = [
                    "owner_id" => [
                        "nullable",
                        "integer",
                        Rule::exists("owners", "id"),
                    ],
                    "service_id" => [
                        "nullable",
                        "integer",
                        Rule::exists("services", "id"),
                    ],
                    "owner_id" => [
                        "nullable",
                        "integer",
                        Rule::exists("owners", "id"),
                    ],
                    "upload_image" => [
                        "required",
                        "image",
                        "max:10240"
                    ],
                    "name" => [
                        "required",
                        "string",
                        "between:0,256",
                    ],
                    "description" => [
                        "nullable",
                        "string",
                        "between:0,2048",
                    ],
                ];
            } else if ($route_name === "api.v1.front.image.good") {
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        })
                    ]
                ];
            } else if ($route_name === "api.v1.front.image.delete") {
                // 未削除の画像を削除する
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type")["off"]);
                        })
                    ]
                ];
            }
        } else if ($method === "GET") {

            if ($route_name === "api.front.image.owner") {
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        })
                    ]
                ];
            } else if ($route_name === "api.front.image.service") {
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        })
                    ]
                ];
            } else if ($route_name === "api.front.image.show") {
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        })
                    ]
                ];
            }
        } else {

        }

        return $rules;
    }
}
