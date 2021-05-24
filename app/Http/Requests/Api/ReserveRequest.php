<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use App\Models\Guest;
use App\Models\Service;
use App\Models\Reserve;
use App\Models\ReservableTime;
use App\Rules\MyDateTime;

class ReserveRequest extends BaseRequest
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

                /*
                {
                    "service_id": 1,
                    "guest_id": 1,
                    "from_datetime": "2021-5-1 12:00:00",
                    "to_datetime": "2021-5-10 12:00:00"
                }
                */
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
                        new MyDateTime(),
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($from === false) {
                                $fail(":attributeのフォーマットが正しくありません｡");
                                return false;
                            }

                            // // 予約開始日時の秒数は[00]秒であること
                            // if ($from->format("s") !== "00") {
                            //     $fail(":attributeの秒数は[00]で指定して下さい｡");
                            // }

                            // 予約開始時のタイムスタンプ
                            $start_timestamp = $from->getTimestamp();

                            // 予約終了日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("to_datetime"));
                            if ($to === false) {
                                $fail(":attributeのフォーマットが正しくありません｡");
                                return false;
                            }
                            // 予約終了時のタイムスタンプ
                            $end_timestamp = $to->getTimestamp();
                            if ( ($start_timestamp < $end_timestamp) !== true) {
                                $fail("正しい予約期間を指定して下さい｡");
                                return false;
                            }


                            $reservation_periods = [];
                            for($index = $start_timestamp; $index <= $end_timestamp; $index++) {
                                if ($index % Config("const.seconds_per_day") === 0) {
                                    $to_timestamp = $index -1;
                                    $reservation_periods[] = [
                                        "from" => $start_timestamp,
                                        "to" => $to_timestamp,
                                        "seconds" => $to_timestamp - $start_timestamp,
                                        "from_format" => (new \DateTime())->setTimestamp($start_timestamp)->format("Y-n-j H:i:s"),
                                        "to_format" => (new \DateTime())->setTimestamp($to_timestamp)->format("Y-n-j H:i:s"),
                                        "reservable_from" => (new \DateTime())->setTimestamp($start_timestamp)->format("H:i"),
                                        "reservable_to" => (new \DateTime())->setTimestamp($to_timestamp)->format("H:i"),
                                        "reservable_day" => (new \Datetime())->setTimestamp($start_timestamp)->format("N"),
                                    ];
                                    $start_timestamp = $index;
                                    continue;
                                }
                                if ($index === $end_timestamp) {
                                    $to_timestamp = $index -1;
                                    $reservation_periods[] = [
                                        "from" => $start_timestamp,
                                        "to" => $to_timestamp,
                                        "seconds" => $to_timestamp - $start_timestamp,
                                        "from_format" => (new \DateTime())->setTimestamp($start_timestamp)->format("Y-n-j H:i:s"),
                                        "to_format" => (new \DateTime())->setTimestamp($to_timestamp)->format("Y-n-j H:i:s"),
                                        "reservable_from" => (new \DateTime())->setTimestamp($start_timestamp)->format("H:i"),
                                        "reservable_to" => (new \DateTime())->setTimestamp($to_timestamp)->format("H:i"),
                                        "reservable_day" => (new \Datetime())->setTimestamp($start_timestamp)->format("N"),
                                    ];
                                    $start_timestamp = $index;
                                }
                            }

                            print_r($reservation_periods);

                            $result = ReservableTime::where(function ($query) use ($reservation_periods) {
                                foreach ($reservation_periods as $key => $value) {
                                    $query->orWhere([
                                        ["service_id", "=", $this->input("service_id")],
                                        ["reservable_from", "<=", $value["reservable_from"]],
                                        ["reservable_to", ">", $value["reservable_to"]],
                                        ["reservable_day", "=", $value["reservable_day"]],
                                    ]);
                                }
                            })
                            ->get();


                            print_r($result->toArray());

                            if ( ($result->count() >= count($reservation_periods)) !== true) {
                                $fail("指定した期間は予約ができません｡");
                                return false;
                            }
                        }
                    ],
                    "to_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-n-j H:i:s",
                        new MyDateTime(),
                        function ($attribute, $value, $fail) {
                            // 予約開始日時のバリデーション
                            $to = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
                            if ($to === false) {
                                $fail(":attributeのフォーマットが正しくありません｡");
                                return false;
                            }

                            // // 予約開始日時の秒数は[00]秒であること
                            // if ($to->format("s") !== "59") {
                            //     $fail(":attributeの秒数は[59]で指定して下さい｡");
                            // }

                            $to = $to->getTimestamp();
                            // 予約終了日時のバリデーション
                            $from = \DateTime::createFromFormat("Y-n-j H:i:s", $this->input("from_datetime"));
                            if ($from === false) {
                                $fail(":attributeのフォーマットが正しくありません｡");
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
                ];
            } else if ($route_name === "api.front.reserve.update") {

                // 予約情報の更新処理
                $rules = [
                    "reserve_id" => [
                        "required",
                        "integer",
                        Rule::exists("reserves", "id"),
                        function ($attribute, $value, $fail) {
                            $reserve = Reserve::where([
                                // セキュリティトークンがマッチすること
                                ["token", "=", $this->route()->parameter("token")],
                                // レンタルサービスIDがマッチしていること
                                ["service_id", "=", $this->input("service_id")],
                                // ゲストIDがマッチしていること
                                ["guest_id", "=", $this->input("guest_id")],
                            ])
                            ->find($value);
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
                    "token" => [
                        "nullable",
                        "string",
                        // 仮押さえしたユーザーと同一かどうか
                        Rule::exists("reserves", "token"),
                        function ($attribute, $value, $fail) {
                            $reserve = Reserve::where("id", $this->route()->parameter("reserve_id"))
                            ->where("token", $value)
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
                        //     ->where("token", $this->route()->parameter("token"))
                        //     ->get()
                        //     ->first();

                        //     // トークンと予約IDがマッチしているかどうか
                        //     if ($reserve === NULL) {
                        //         $fail("指定した予約情報を更新できません｡");
                        //     }
                        // }
                    ],
                    // "token" => [
                    //     "required",
                    //     "string",
                    //     // 仮押さえしたユーザーと同一かどうか
                    //     Rule::exists("reserves", "token"),
                    //     function ($attribute, $value, $fail) {
                    //         $reserve = Reserve::where("id", $this->route()->parameter("reserve_id"))
                    //         ->where("token", $value)
                    //         ->get()
                    //         ->first();

                    //         // トークンと予約IDがマッチしているかどうか
                    //         if ($reserve === NULL) {
                    //             $fail("指定した予約情報を更新できません｡");
                    //         }
                    //     }
                    // ]
                ];
            } else if ($route_name === "api.front.reserve.list") {
                $rules = [];
            }
        }


        return $rules;
    }

    public function messages()
    {
        return [
            // 予約ID
            "reserve_id.required" => "サービスID",
            "reserve_id.integer" => "サービスID",
            // サービスID
            "service_id.required" => "サービスID",
            "service_id.integer" => "サービスID",
            // ゲストID
            "guest_id.required" => "ゲストID",
            "guest_id.integer" => "ゲストID",
            // 予約期間
            "from_datetime.required" => "予約開始時間",
            "from_datetime.date_format" => "予約開始時間",
            "to_datetime.required" => "予約終了時間",
            "to_datetime.date_format" => "予約終了時間",
            // 認証用トークン
            "token.required" => ":attributeは必須項目です｡",
            "token.string" => ":attributeは文字列で指定して下さい｡",
        ];
    }

    public function attributes()
    {
        return [
            "reserve_id" => "予約ID",
            "service_id" => "サービスID",
            "guest_id" => "ゲストID",
            "from_datetime" => "予約開始時間",
            "to_datetime" => "予約終了時間",
            "token" => "トークン",
        ];
    }
}
