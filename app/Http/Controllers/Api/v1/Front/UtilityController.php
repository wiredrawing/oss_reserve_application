<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UtilityRequest;
use Illuminate\Http\Request;

class UtilityController extends Controller
{

    /**
     * 登録可能なサービスタイプを取得する
     *
     * @param UtilityRequest $request
     * @return void
     */
    public function service_type(UtilityRequest $request)
    {
        try {
            $service_types = Config("const.service_types");
            $response = [
                "status" => true,
                "data" => $service_types,
            ];
            // 現在､予約可能なサービス一覧を表示
            return response()->json($response);
        } catch (\Throwable $e) {
            logger()->error($e);
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }
}
