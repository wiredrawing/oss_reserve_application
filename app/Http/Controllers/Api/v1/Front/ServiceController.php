<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ServiceRequest;
use App\Models\Service;
use App\Models\Reserve;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ServiceController extends Controller
{


    /**
     * 新規サービスの登録処理
     *
     * @param ServiceRequest $request
     * @return void
     */
    public function create (ServiceRequest $request)
    {
        try {
            $post_data = $request->validated();

            // 新規サービス登録
            $service = Service::create($post_data);

            $response = [
                "status" => true,
                "data" => $service,
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
     * 指定したサービス情報の更新処理
     *
     * @param ServiceRequest $request
     * @param integer $service_id
     * @return void
     */
    public function update (ServiceRequest $request, int $service_id)
    {
        try {
            $post_data = $request->validated();

            // 更新対象のレコード取得
            $service = Service::find($service_id);
            var_dump("更新前");
            print_r($service->toArray());
            // レコードの更新処理
            $result = $service->fill($post_data)->save();
            var_dump("更新あと");
            print_r($service->toArray());
            // SQLクエリの成功可否
            if ($result !== true) {
                throw new \Exception("指定したサービス情報のアップデートに失敗しました｡");
            }

            $response = [
                "status" => true,
                "data" => $service,
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
     * 現在予約可能なサービス一覧を取得する
     *
     * @param ServiceRequest $request
     * @return void
     */
    public function list(ServiceRequest $request)
    {
        try {
            $services = Service::where([
                "is_displayed" => Config("const.binary_type.on"),
                "is_deleted" => Config("const.binary_type.off"),
            ])
            ->get();
            $response = [
                "status" => true,
                "data" => $services,
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
     * 指定したサービスの全予約状況を取得する
     *
     * @param ServiceRequest $request
     * @param integer $service_id
     * @return void
     */
    public function schedule(ServiceRequest $request, int $service_id)
    {
        try {
            $get_data = $request->validated();
            logger()->info($get_data);

            $service = Service::with([
                "reserves" => function ($query) {
                    $query
                    ->where("is_confirmed", Config("const.binary_type.on"))
                    ->where("is_canceled", Config("const.binary_type.off"))
                    // リクエスト日時移行のスケジュールを取得
                    ->where("to_datetime", ">=", date("Y-m-d H:i:s"))
                    ->orderBy("to_datetime", "asc");
                }
            ])
            ->find($service_id);

            $response = [
                "status" => true,
                "data" => $service,
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
     * 指定したサービスの指定した時間帯でのダブルブッキングチェック
     *
     * @param ServiceRequest $request
     * @param integer $service_id
     * @param integer $reserve_id
     * @return void
     */
    public function duplication_check(ServiceRequest $request, int $service_id, int $reserve_id)
    {
        try {
            $get_data = $request->validated();
            // 対象の予約情報を取得
            $reservation = Reserve::where("service_id", $service_id)
            ->where("is_confirmed", Config("const.binary_type.on"))
            ->where("is_canceled", Config("const.binary_type.off"))
            ->with([
                "service",
            ])
            ->find($reserve_id);

            if ($reservation === NULL) {
                throw new \Exception("指定した予約情報が見つかりません｡");
            }

            // 以下指定した時間帯での重複ブッキングを検出する
            $duplicated_reservation = Reserve::where("service_id", $get_data["service_id"])
            ->where("id", "!=", $get_data["reserve_id"])
            ->where("is_confirmed", Config("const.binary_type.on"))
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where("from_datetime", "<=", $reservation->to_datetime)
            ->where("to_datetime", ">", $reservation->from_datetime)
            ->get();

            $response = [
                "status" => true,
                "data" => $duplicated_reservation,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }
}
