<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class OwnerRequest extends BaseRequest
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
        $route_name = Route::currentRouteName();
        $method = strtoupper($this->getMethod());


        if ($method === "GET") {
            if ($route_name === "api.front.owner.list") {
                $rules = [];
            } else if ($route_name === "api.front.owner.detail") {
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        }),
                    ]
                ];
            }
        } else if ($method === "POST") {
            if ($route_name === "api.front.owner.create") {
                $rules = [
                    "owner_name" => [
                        "required",
                        "string",
                        "between:1,255",
                    ],
                    "owner_name_sort" => [
                        "required",
                        "string",
                        "between:1,255",
                    ],
                    "phone_number" => [
                        "required",
                        "string",
                        "between:1,16",
                    ],
                    "email" => [
                        "required",
                        "string",
                        "email:rfc",
                    ],
                    "description" => [
                        "nullable",
                        "string",
                        "between:0,2048",
                    ]
                ];
            } else if ($route_name === "api.front.owner.update") {
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query
                            ->where("is_deleted", Config("const.binary_type")["off"]);
                        }),
                    ],
                    "owner_name" => [
                        "required",
                        "string",
                        "between:1,255",
                    ],
                    "owner_name_sort" => [
                        "required",
                        "string",
                        "between:1,255",
                    ],
                    "phone_number" => [
                        "required",
                        "string",
                        "between:1,16",
                    ],
                    "email" => [
                        "required",
                        "string",
                        "email:rfc",
                    ],
                    "description" => [
                        "nullable",
                        "string",
                        "between:0,2048",
                    ]
                ];
            }

        }

        return $rules;
    }

}
