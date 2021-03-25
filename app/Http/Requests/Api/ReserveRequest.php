<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Guest;
use App\Models\Service;
use App\Models\Reserve;
use Symfony\Component\HttpFoundation\ServerBag;

class ReserveRequest extends FormRequest
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
            if ($route_name === "api.front.reserve.create" || $route_name === "api.front.reserve.validate") {

                // 共有ルール
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        function ($attribute, $value, $fail) {
                            $service = Service::where("is_displayed", Config("const.binary_type.on"))
                            ->where("is_deleted", Config("const.binary_type.off"))
                            ->find($value);
                            if ($service === NULL) {
                                $fail("指定した:attributeが見つかりません｡");
                            }
                        }
                    ],
                    "guest_id" => [
                        "nullable",
                        "integer",
                        function ($attribute, $value, $fail) {
                            $guest = Guest::where("is_displayed", Config("const.binary_type.on"))
                            ->where("is_deleted", Config("const.binary_type.off"))
                            ->find($value);
                            if ($guest === NULL) {
                                $fail("指定した:attributeが見つかりません｡");
                            }
                        }
                        // Rule::exists("guests", "id"),
                    ],
                    "from_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-n-j H:i:s",
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($from === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $from = $from->getTimestamp();

                            // 予約終了日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("to_datetime"));
                            if ($to === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $to = $to->getTimestamp();
                            if ( ($from < $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡".__LINE__);
                                return false;
                            }
                        }
                    ],
                    "to_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-n-j H:i:s",
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($to === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $to = $to->getTimestamp();
                            // 予約終了日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("from_datetime"));
                            if ($from === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $from = $from->getTimestamp();
                            if ( ($from < $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡");
                                return false;
                            }
                        }
                    ],
                    "memo" => [
                        "nullable",
                        "string",
                        "between:0,1024",
                    ],
                    "user_token" => [
                        "nullable",
                        "string",
                        // 仮押さえしたユーザーと同一かどうか
                        Rule::exists("reserves", "user_token")
                    ]
                ];
            } else if ($route_name === "api.front.reserve.update") {

                // 予約情報の更新処理
                $rules = [
                    "reserve_id" => [
                        "required",
                        "integer",
                        Rule::exists("reserves", "id"),
                        function ($attribute, $value, $fail) {
                            $reserve = Reserve::where("id", $value)
                            // セキュリティトークンがマッチすること
                            ->where("user_token", $this->route()->parameter("user_token"))
                            // レンタルサービスIDがマッチしていること
                            ->where("service_id", $this->input("service_id"))
                            ->get()
                            ->first();
                            if ($reserve === NULL) {
                                $fail("指定した予約情報が見つかりませんでした｡");
                            }
                        }
                    ],
                    "service_id" => [
                        "required",
                        "integer",
                        // Rule::exists("services", "id"),
                    ],
                    "guest_id" => [
                        "nullable",
                        "integer",
                        // Rule::exists("guests", "id"),
                    ],
                    "from_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-n-j H:i:s",
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($from === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $from = $from->getTimestamp();

                            // 予約終了日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("to_datetime"));
                            if ($to === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $to = $to->getTimestamp();
                            if ( ($from < $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡".__LINE__);
                                return false;
                            }
                        }
                    ],
                    "to_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-n-j H:i:s",
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($to === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $to = $to->getTimestamp();
                            // 予約終了日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("from_datetime"));
                            if ($from === false) {
                                $fail("予約日時のフォーマットが正しくありません｡");
                                return false;
                            }
                            $from = $from->getTimestamp();
                            if ( ($from < $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡");
                                return false;
                            }
                        }
                    ],
                    "memo" => [
                        "nullable",
                        "string",
                        "between:0,1024",
                    ],
                    "user_token" => [
                        "nullable",
                        "string",
                        // 仮押さえしたユーザーと同一かどうか
                        Rule::exists("reserves", "user_token"),
                        function ($attribute, $value, $fail) {
                            $reserve = Reserve::where("id", $this->route()->parameter("reserve_id"))
                            ->where("user_token", $value)
                            ->get()
                            ->first();
                            if ($reserve === NULL) {
                                $fail("指定した予約情報を更新できません｡");
                            }
                        }
                    ]
                ];
            }
        } else if ($method === "GET") {
            if ($route_name === "api.front.reserve.detail") {
                $rules = [
                    "reserve_id" => [
                        "required",
                        "integer",
                        Rule::exists("reserves", "id"),
                        // function ($attribute, $value, $fail) {
                        //     $reserve = Reserve::where("id", $value)
                        //     ->where("user_token", $this->route()->parameter("user_token"))
                        //     ->get()
                        //     ->first();

                        //     // トークンと予約IDがマッチしているかどうか
                        //     if ($reserve === NULL) {
                        //         $fail("指定した予約情報を更新できません｡");
                        //     }
                        // }
                    ],
                    // "user_token" => [
                    //     "required",
                    //     "string",
                    //     // 仮押さえしたユーザーと同一かどうか
                    //     Rule::exists("reserves", "user_token"),
                    //     function ($attribute, $value, $fail) {
                    //         $reserve = Reserve::where("id", $this->route()->parameter("reserve_id"))
                    //         ->where("user_token", $value)
                    //         ->get()
                    //         ->first();

                    //         // トークンと予約IDがマッチしているかどうか
                    //         if ($reserve === NULL) {
                    //             $fail("指定した予約情報を更新できません｡");
                    //         }
                    //     }
                    // ]
                ];
            }
        }


        return $rules;
    }


    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters());
    }


    public function messages()
    {
        return [

        ];
    }

    public function attributes()
    {
        return [

        ];
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
