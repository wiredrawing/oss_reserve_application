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

    /**
     * 予約可能な曜日一覧を取得する
     *
     * @param UtilityRequest $request
     * @return void
     */
    public function reservable_days(UtilityRequest $request)
    {
        try {
            $reservable_days = Config("const.reservable_days");

            $response = [
                "status" => true,
                "data" => $reservable_days,
            ];
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

    /**
     * 予約可能な時一覧を取得する
     *
     * @param UtilityRequest $request
     * @return void
     */
    public function reservable_hours(UtilityRequest $request)
    {
        try {
            $reservable_hours = Config("const.reservable_hours");

            $response = [
                "status" => true,
                "data" => $reservable_hours,
            ];
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

    /**
     * 予約可能な分一覧を取得する
     *
     * @param UtilityRequest $request
     * @return void
     */
    public function reservable_minutes(UtilityRequest $request)
    {
        try {
            $reservable_minutes = Config("const.reservable_minutes");

            $response = [
                "status" => true,
                "data" => $reservable_minutes,
            ];
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
