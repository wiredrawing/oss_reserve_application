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
                    "reservable_times.*.reservable_day" => [
                        "required",
                        "integer",
                        Rule::in(array_column(Config("const.reservable_days"), "index")),
                    ],
                    "reservable_times.*.reservable_from" => [
                        // 予約可能開始時間
                        "required",
                        Rule::in(Config("const.reservable_hours")),
                    ],
                    "reservable_times.*.reservable_to" => [
                        // 予約可能終了時間
                        "required",
                        Rule::in(Config("const.reservable_minutes")),
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

    public function attributes()
    {
        $attributes = [
            "owner_id" => "オーナー名",
            "service_name" => "サービス名",
            "service_type" => "サービスの形態･タイプなど",
            "price" => "一括単価",
            "price_per_hour" => "時間給",
            "capacity" => "収容人数",
            "reservable_day" => "予約可能曜日",
            "reservable_from" => "指定曜日に可能な予約開始時間",
            "reservable_to" => "指定曜日に可能な予約終了時間",
            "reservable_from_hour" => "-",
            "reservable_to_hour" => "-",
            "reservable_from_minute" => "-",
            "reservable_to_minute" => "-",
        ];
        foreach ($this->input("reservable_times") as $key => $value) {
            foreach ($value as $inner_key => $inner_value) {
                if (isset($attributes[$inner_key])) {
                    $attributes["reservable_times.{$key}.{$inner_key}"] = "{$key}番目の{$attributes[$inner_key]}";
                }
            }
        }
        return $attributes;
    }

    //エラー時HTMLページにリダイレクトされないようにオーバーライド
    protected function failedValidation(Validator $validator)
    {
        $validation_errors = $validator->errors()->toArray();
        // 配列を階層化させたいキー
        $target_key = "reservable_times";
        $_errors = [];
        foreach ($this->input($target_key) as $key => $value) {
            $_errors[$target_key][$key] = [];
            foreach ($validation_errors as $inner_key => $inner_value) {
                $key_you_want_to_delete = $target_key.".".$key.".";
                if (strpos($inner_key, $key_you_want_to_delete) !== false) {
                    $extracted_key = \str_replace($key_you_want_to_delete, "", $inner_key);
                    $_errors[$target_key][$key][$extracted_key] = $inner_value;
                }
            }
        }
        $validation_errors[$target_key] = $_errors[$target_key];
        $response = [
            "status" => false,
            "data" => $validation_errors,
        ];
        throw new HttpResponseException(
            response()->json(
                $response,
                // $validator->errors()->getMessages(),
                200
            )
        );
    }

}
