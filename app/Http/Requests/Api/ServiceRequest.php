<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ServiceRequest extends BaseRequest
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

            // 新規サービス登録
            if ($route_name === "api.front.service.create") {
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id"),
                    ],
                    "service_name" => [
                        "required",
                        "string",
                        "between:1,512"
                    ],
                    "memo" => [
                        "nullable",
                        "string",
                        "between:0,10240"
                    ],
                    // 当該のサービスが収容できる人数
                    "capacity" => [
                        "required",
                        "integer",
                        "min:1",
                    ],
                    // 統一価格
                    "price" => [
                        "required",
                        "integer",
                    ],
                    // 時間単価(時給)
                    "price_per_hour" => [
                        "required",
                        "integer",
                    ],
                    "service_type" => [
                        "required",
                        "integer",
                        Rule::in(array_column(Config("const.service_types"), "index"))
                    ],
                    "reservable_times" => [
                        "required",
                        "array",
                    ],
                    "reservable_times.*.reservable_date" => [
                        // 予約可能曜日
                    ],
                    "reservable_times.*.reservable_from" => [
                        // 予約可能開始時間
                    ],
                    "reservable_times.*.reservable_to" => [
                        // 予約可能終了時間
                    ],
                ];
            } else if ($route_name === "api.front.service.update") {
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        // 生存中サービスのみ
                        Rule::exists("services", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                    ],
                    "owner_id" => [
                        "nullable",
                    ],
                    "service_name" => [
                        "required",
                        "string",
                        "between:1,512"
                    ],
                    "memo" => [
                        "nullable",
                        "string",
                        "between:0,10240"
                    ],
                    // 当該のサービスが収容できる人数
                    "capacity" => [
                        "required",
                        "integer",
                        "min:1",
                    ],
                    // 統一価格
                    "price" => [
                        "required",
                        "integer",
                    ],
                    // 時間単価(時給)
                    "price_per_hour" => [
                        "required",
                        "integer",
                    ],
                    "service_type" => [
                        "required",
                        "integer",
                    ]
                ];
            }
        } else if ($method === "GET") {

            if ($route_name === "api.front.service.detail") {
                // 指定したservice_idに紐づくサービス情報を取得する
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        // 生存中サービスのみ
                        Rule::exists("services", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                    ],
                ];
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
            } else if ($route_name === "api.front.service.list") {
                // 予約可能なサービス一覧を取得する
                $rules = [];
            }
        } else {
            $rules = [];
        }

        return $rules;
    }


}
