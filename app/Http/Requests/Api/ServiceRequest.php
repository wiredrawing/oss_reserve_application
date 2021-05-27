<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Service;
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
            if ($route_name === "api.front.service.create" || $route_name === "api.front.service.check") {
                /*
                    {
                        "service_images": [],
                        "reservable_times": [
                            {
                                "reservable_day": 1,
                                "reservable_from": "09:00",
                                "reservable_to": "16:00",
                                "reservable_from_hour": "09",
                                "reservable_from_minute": "00",
                                "reservable_to_hour": "16",
                                "reservable_to_minute": "00",
                                "memo": "エンドユーザー向け注意事項",
                                "memo_for_admin": "管理者向け注意事項"
                            },
                            {
                                "reservable_day": 7,
                                "reservable_from": "09:00",
                                "reservable_to": "16:00",
                                "reservable_from_hour": "09",
                                "reservable_from_minute": "00",
                                "reservable_to_hour": "16",
                                "reservable_to_minute": "00",
                                "memo": "エンドユーザー向け注意事項",
                                "memo_for_admin": "管理者向け注意事項"
                            }
                        ],
                        "service_name": "サービス名",
                        "service_type": 2,
                        "price_per_hour": "15000",
                        "capacity": "1",
                        "owner_id": 9,
                        "memo": "管理側メモ"
                    }
                */
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id"),
                    ],
                    "service_name" => [
                        "required",
                        "string",
                        "between:1,512",
                        function ($attribute, $value, $fail) {
                            $service = Service::where([
                                ["service_name", "=", $value],
                                ["service_type", "=", $this->input("service_type")]
                            ])
                            ->get()
                            ->first();
                            if ($service !== NULL) {
                                $fail("既に同じサービスが登録されています｡");
                                return false;
                            }
                        }
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
                    // 予約開始時間
                    "reservable_times.*.reservable_from_hour" => [
                        "required",
                        Rule::in(Config("const.reservable_hours")),
                        function ($attribute, $value, $fail) {
                            // バリデーション中のindexを保持
                            $current_index = explode(".", $attribute)[1];
                            $hour = $value;
                            $minute = $this->input("reservable_times.{$current_index}.reservable_to_minute");
                            // 24:00を超えてないことをチェックする
                            if ($hour === "24" && $minute !== "00") {
                                $fail(":attributeは24:00を超えることはできません｡");
                                return false;
                            }
                        },
                    ],
                    "reservable_times.*.reservable_from_minute" => [
                        "required",
                        Rule::in(Config("const.reservable_minutes")),
                    ],
                    // 予約終了時間
                    "reservable_times.*.reservable_to_hour" => [
                        "required",
                        Rule::in(Config("const.reservable_hours")),
                        function ($attribute, $value, $fail) {
                            // バリデーション中のindexを保持
                            $current_index = explode(".", $attribute)[1];
                            $hour = $value;
                            $minute = $this->input("reservable_times.{$current_index}.reservable_to_minute");
                            // 24:00を超えてないことをチェックする
                            if ($hour === "24" && $minute !== "00") {
                                $fail(":attributeは24:00を超えることはできません｡");
                                return false;
                            }
                        },
                    ],
                    "reservable_times.*.reservable_to_minute" => [
                        "required",
                        Rule::in(Config("const.reservable_minutes")),
                    ],
                ];
            } else if ($route_name === "api.front.service.update") {
                $rules = [
                    "id" => [
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
                        "between:1,512",
                        function ($attribute, $value, $fail) {
                            var_dump($this->input("id"));
                            var_dump($this->route()->parameter("id"));
                            // 編集中サービス以外で､同じサービス名が存在しないかどうかを検証
                            $service = Service::where([
                                ["service_name", "=", $value],
                                ["id", "<>", $this->input("id")]
                            ])
                            ->get()
                            ->first();
                            if ($service !== NULL)  {
                                $fail("既に同じサービスが登録されています｡");
                                return false;
                            }
                        }
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
                    // 時間単価(時給)
                    "price_per_hour" => [
                        "required",
                        "integer",
                        "between:1,100000"
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
                    // 予約開始時間のバリデーション
                    "reservable_times.*.reservable_from" => [
                        "required",
                    ],
                    "reservable_times.*.reservable_from_hour" => [
                        "required",
                        Rule::in(Config("const.reservable_hours")),
                        function ($attribute, $value, $fail) {
                            // バリデーション中のindexを保持
                            $current_index = explode(".", $attribute)[1];
                            $hour = $value;
                            $minute = $this->input("reservable_times.{$current_index}.reservable_from_minute");
                            // 24:00を超えてないことをチェックする
                            if ($hour === "24" && $minute !== "00") {
                                $fail(":attributeは24:00を超えることはできません｡");
                                return false;
                            }
                        },
                    ],
                    "reservable_times.*.reservable_from_minute" => [
                        "required",
                        Rule::in(Config("const.reservable_minutes")),
                    ],
                    // 予約終了時間のバリデーション
                    "reservable_times.*.reservable_to" => [
                        "required",
                    ],
                    "reservable_times.*.reservable_to_hour" => [
                        "required",
                        Rule::in(Config("const.reservable_hours")),
                        function ($attribute, $value, $fail) {
                            // バリデーション中のindexを保持
                            $current_index = explode(".", $attribute)[1];
                            $hour = $value;
                            $minute = $this->input("reservable_times.{$current_index}.reservable_to_minute");
                            // 24:00を超えてないことをチェックする
                            if ($hour === "24" && $minute !== "00") {
                                $fail(":attributeは24:00を超えることはできません｡");
                                return false;
                            }
                        },
                    ],
                    "reservable_times.*.reservable_to_minute" => [
                        "required",
                        Rule::in(Config("const.reservable_minutes")),
                    ],
                ];
            } else if ($route_name === "api.front.service.exclude_date") {
                // 臨時休業日の設定
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
            "price_per_hour" => "時間給",
            "capacity" => "収容人数",
            "reservable_day" => "予約可能曜日",
            // 予約開始時間
            "reservable_from" => "指定曜日に可能な予約開始時間",
            "reservable_from_hour" => "指定曜日に可能な予約開始時間",
            "reservable_from_minute" => "指定曜日に可能な予約開始時間",
            // 予約終了時間
            "reservable_to" => "指定曜日に可能な予約終了時間",
            "reservable_to_hour" => "指定曜日に可能な予約終了時間",
            "reservable_to_minute" => "指定曜日に可能な予約終了時間",
        ];
        // 予約可能時間帯が設定されている場合
        $reservable_times = $this->input("reservable_times");
        if (is_array($reservable_times) && count($reservable_times) > 0) {
            foreach ($reservable_times as $key => $value) {
                foreach ($value as $inner_key => $inner_value) {
                    if (isset($attributes[$inner_key])) {
                        $attributes["reservable_times.{$key}.{$inner_key}"] = "{$key}番目の{$attributes[$inner_key]}";
                    }
                }
            }
        }
        return $attributes;
    }

    public function messages ()
    {
        $messages = [
            "owner_id.required"       => ":attributeは必須項目です｡",
            "owner_id.integer"        => ":attributeは数値で指定して下さい｡",
            "owner_id.exists"         => ":attributeが存在しません｡",
            "service_name.required"   => ":attributeは必須項目です｡",
            "service_name.string"     => ":attributeは1文字以上､500文字以内で入力して下さい｡",
            "service_type.required"   => ":attributeは必須項目です｡",
            "service_type.integer"    => ":attributeは数値で入力して下さい｡",
            "price_per_hour.required" => ":attributeは必須項目です｡",
            "price_per_hour.between"  => ":attributeは100,000円以下で入力して下さい｡",
            "price_per_hour.integer"  => ":attributeは数値のみで入力して下さい｡",
            "capacity.required"       => ":attributeは必須項目です｡",
        ];

        foreach ($this->attributes() as $key => $value) {
            if (strpos($key, "reservable_times") !== false) {
                $messages[$key.".required"] = ":attributeは必須項目です｡";
            }
        }

        return $messages;
    }


    //エラー時HTMLページにリダイレクトされないようにオーバーライド
    protected function failedValidation(Validator $validator)
    {
        $validation_errors = $validator->errors()->toArray();

        // 配列を階層化させたいキー
        $target_key = "reservable_times";
        $_errors = [];
        $reservable_times = $this->input($target_key);
        if (is_array($reservable_times) && count($reservable_times) > 0) {
            foreach ($reservable_times as $key => $value) {
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
        }


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
