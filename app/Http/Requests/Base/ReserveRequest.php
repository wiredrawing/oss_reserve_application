<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use App\Models\Guest;

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
                    "room_id" => [
                        "required",
                        "integer",
                        Rule::exists("rooms", "id"),
                    ],
                    "guest_id" => [
                        "required",
                        "integer",
                        Rule::exists("guests", "id"),
                    ],
                    "from_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-m-d H:i:s",
                        function ($attribute, $value, $fail, $all) {
                            $from = (\DateTime::createFromFormat("Y-m-d H:i:s", $value))->getTimestamp();
                            $to = (\DateTime::createFromFormat("Y-m-d H:i:s", $this->input("to_datetime")))->getTimestamp();
                            if ( ($from > $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡");
                            }
                        }
                    ],
                    "to_datetime" => [
                        "required",
                        "string",
                        "date_format:Y-m-d H:i:s",
                        function ($attribute, $value, $fail, $all) {
                            $from = (\DateTime::createFromFormat("Y-m-d H:i:s", $this->input("from_datetime")))->getTimestamp();
                            $to = (\DateTime::createFromFormat("Y-m-d H:i:s", $value))->getTimestamp();
                            if ( ($from > $to) !== true) {
                                $fail("正しい予約期間を指定して下さい｡");
                            }
                        }
                    ],
                    "memo" => [
                        "nullable",
                        "string",
                        "between:0,1024",
                    ]
                ];
            }
        } else if ($method === "GET") {


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
}
