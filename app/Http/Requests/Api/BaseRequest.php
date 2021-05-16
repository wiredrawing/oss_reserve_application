<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class BaseRequest extends FormRequest
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



        return $rules;
    }


    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters());
    }

    //エラー時HTMLページにリダイレクトされないようにオーバーライド
    protected function failedValidation(Validator $validator)
    {
        $response = [
            "status" => false,
            "data" => $validator->errors()
        ];
        // throw new HttpResponseException(
        //     response()->json(
        //         $response,
        //         // $validator->errors()->getMessages(),
        //         200
        //     )
        // );
        throw new HttpResponseException(
            response()->json(
                $response,
                200
            )
        );
    }
}
