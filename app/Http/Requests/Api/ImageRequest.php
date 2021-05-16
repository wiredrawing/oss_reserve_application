<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseRequest;
use App\Models\ServiceImage;
use App\Models\OwnerImage;
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
                    // "owner_id" => [
                    //     "nullable",
                    //     "integer",
                    //     Rule::exists("owners", "id"),
                    // ],
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
                            ->where("is_deleted", Config("const.binary_type.off"));
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
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ]
                ];
            } else if ($route_name === "api.front.image.service.add") {
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ],
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                        // duplication check
                        function ($attribute, $value, $fail) {
                            $number = ServiceImage::where([
                                ["service_id", "=", $value],
                                ["image_id", "=", $this->input("image_id")],
                            ])->count();
                            // if it is greater than 0, return an error message.
                            if ($number !== 0) {
                                $fail("既に登録済みです｡");
                            }
                        }
                    ]
                ];
            } else if ($route_name === "api.front.image.service.delete") {
                // serviceに紐付いた画像を削除
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ],
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                        // duplication check
                        function ($attribute, $value, $fail) {
                            $number = ServiceImage::where([
                                ["service_id", "=", $value],
                                ["image_id", "=", $this->input("image_id")],
                            ])->count();
                            // if it is greater than 0, return an error message.
                            if ($number !== 1) {
                                // serviceと画像の紐付けがみつからない場合
                                $fail("指定した画像はサービスに紐づけられていません｡");
                            }
                        }
                    ]
                ];
            } else if ($route_name === "api.front.image.owner.add") {
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ],
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                        // duplication check
                        function ($attribute, $value, $fail) {
                            $number = OwnerImage::where([
                                ["owner_id", "=", $value],
                                ["image_id", "=", $this->input("image_id")],
                            ])->count();
                            // if it is greater than 0, return an error message.
                            if ($number !== 0) {
                                $fail("既に登録済みです｡");
                            }
                        }
                    ]
                ];
            } else if ($route_name === "api.front.image.owner.delete") {
                // ownerに紐付いた画像を削除する
                $rules = [
                    "image_id" => [
                        "required",
                        "integer",
                        Rule::exists("images", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ],
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query->where("is_deleted", Config("const.binary_type.off"));
                        }),
                        // duplication check
                        function ($attribute, $value, $fail) {
                            $number = OwnerImage::where([
                                ["owner_id", "=", $value],
                                ["image_id", "=", $this->input("image_id")],
                            ])->count();
                            if ($number !== 1) {
                                // ownerと画像の紐付けがみつからない場合
                                $fail("指定した画像はオーナーに紐づけられていません｡");
                            }
                        }
                    ]
                ];
            }

        } else if ($method === "GET") {

            if ($route_name === "api.front.image.owner.list") {
                $rules = [
                    "owner_id" => [
                        "required",
                        "integer",
                        Rule::exists("owners", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ]
                ];
            } else if ($route_name === "api.front.image.service.list") {
                $rules = [
                    "service_id" => [
                        "required",
                        "integer",
                        Rule::exists("services", "id")->where(function ($query) {
                            $query
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type.off"));
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
                            ->where("token", $this->route()->parameter("token"))
                            ->where("is_displayed", Config("const.binary_type")["on"])
                            ->where("is_deleted", Config("const.binary_type.off"));
                        })
                    ],
                    "token" => [
                        "required",
                        "string",
                    ]
                ];
            } else if ($route_name === "api.front.image.list") {
                $rules = [
                    "offset" => [
                        "nullable",
                        "integer",
                        "min:0",
                    ],
                    "limit" => [
                        "nullable",
                        "integer",
                        "min:0",
                    ],
                ];
            }
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            "upload_image" => "アップロード画像",
            "name" => "アップロード画像の名前",
            "description" => "アップロード画像の概要",
            "owner_id" => "オーナーID",
            "service_id" => "サービスID",
            "token" => "トークン",
        ];
    }

}
