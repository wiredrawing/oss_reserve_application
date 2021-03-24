<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ServiceRequest extends FormRequest
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

        if ($method === "POST") {

        } else if ($method === "GET") {

            if ($route_name === "api.front.service.detail") {

            } else if ($route_name === "api.front.service.schedule") {
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type.on"))
                            ->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ]
                ];
            } else if ($route_name === "api.front.service.duplication_check") {
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id"),
                    ],
                    "reserve_id" => [
                        "required",
                        "integer",
                        Rule::exists("reserves", "id")->where(function ($query) {
                            $query->where("service_id", $this->route()->parameter("service_id"));
                        }),
                    ]
                ];
            }
        } else {
            $rules = [];
        }

        return $rules;
    }



    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters());
    }


    //エラー時HTMLページにリダイレクトされないようにオーバーライド
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                $validator->errors(),
                422
            )
        );
    }
}
