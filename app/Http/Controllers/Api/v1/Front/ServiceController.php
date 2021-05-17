<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ServiceRequest;
use App\Models\Service;
use App\Models\Reserve;
use App\Models\ReservableTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ServiceController extends Controller
{


    /**
     * サービス情報の入力内容検証
     *
     * @param ServiceRequest $request
     * @return void
     */
    public function check (ServiceRequest $request)
    {
        try {
            $post_data = $request->validated();
            $response = [
                "status" => true,
                "data" => $post_data,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollback();
            logger()->error($e);
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }

    /**
     * 新規サービスの登録処理
     *
     * @param ServiceRequest $request
     * @return void
     */
    public function create (ServiceRequest $request)
    {
        try {
            DB::beginTransaction();

            $post_data = $request->validated();

            // 新規サービス登録
            $service = Service::create($post_data);
            $service_id = $service->id;

            // 親テーブルのservicesへのinsertチェック
            if ($service === NULL) {
                throw new \Exception ("service情報の登録に失敗しました｡");
            }

            // 予約可能時間帯を登録する
            $reservable_times = $post_data["reservable_times"];
            foreach ($reservable_times as $key => $value) {
                $value["service_id"] = $service_id;
                $reservable_time = ReservableTime::create($value);
            }

            // 2つのテーブルへsqlクエリが完了したら､$service_idを元に､レコードを再度取得する

            $service = Service::with([
                "reservable_times"
            ])
            ->find($service_id);
            if ($service === NULL) {
                throw new \Exception("サービス情報の登録に失敗しました｡");
            }

            DB::commit();

            $response = [
                "status" => true,
                "data" => $service,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollback();
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
            DB::beginTransaction();

            $post_data = $request->validated();

            // 排他ロックでレコードを取得する
            $service = Service::lockForUpdate()
            ->where("id", $service_id)
            ->get()
            ->first();

            // レコード存在チェック
            if ($service === NULL) {
                throw new \Exception("指定したサービス情報が見つかりません｡");
            }

            // 親のサービス情報を更新
            $result = $service->fill($post_data)->save();

            // SQLクエリの成功可否
            if ($result !== true) {
                throw new \Exception("指定したサービス情報のアップデートに失敗しました｡");
            }

            // サブテーブルのreservable_timesテーブルを更新
            // 同サービスの予約可能時間を削除
            $reservable_times = ReservableTime::where([
                ["service_id", "=", $service->id]
            ])
            ->delete();
            // 再度同サービスの予約可能時間を登録し直す
            $reservable_times = $post_data["reservable_times"];
            foreach ($reservable_times as $key => $value) {
                $value["service_id"] = $service->id;
                $reservable_time = ReservableTime::create($value);
                if ($reservable_time === NULL) {
                    throw new \Exception("予約可能時間の登録に失敗しました｡");
                }
            }

            DB::commit();
            $response = [
                "status" => true,
                "data" => $service,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollback();
            logger()->error($e);
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }

    /**
     * 指定したサービスIDに紐づくサービス情報を返却する
     *
     * @param ServiceRequest $request
     * @param integer $service_id
     * @return void
     */
    public function detail(ServiceRequest $request, int $service_id)
    {
        try {
            $service = Service::with([
                "reserves",
                "service_images",
                "service_images.image",
                "owner",
                "reservable_times",
            ])
            ->where([
                ["is_displayed", "=", Config("const.binary_type.on")],
                ["is_deleted", "=", Config("const.binary_type.off")]
            ])
            ->find($service_id);

            if ($service === NULL) {
                throw new \Exception("指定した予約可能なサービスが見つかりませんでした｡");
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
     * 現在予約可能なサービス一覧かつ､そのサービスの予約状況を取得する
     *
     * @param ServiceRequest $request
     * @return void
     */
    public function list(ServiceRequest $request)
    {
        try {
            $services = Service::with([
                "reserves",
                "owner",
                "service_images",
                "service_images.image",
            ])->where([
                ["is_displayed", "=", Config("const.binary_type.on")],
                ["is_deleted", "=", Config("const.binary_type.off")],
            ])
            ->orderBy("id", "desc")
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

            $reserves = Reserve::with([
                "guest",
                "service",
            ])->where([
                ["is_canceled", "=", Config("const.binary_type.off")],
                ["to_datetime", ">=", date("Y-m-d H:i:s")],
                ["service_id", "=", $service_id]
            ])
            ->orderBy("to_datetime", "asc")
            ->get();

            $response = [
                "status" => true,
                "data" => $reserves,
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
            $reservation = Reserve::where([
                ["service_id", "=", $service_id],
                ["is_canceled", "=", Config("const.binary_type.off")],
            ])
            ->with([
                "service",
                "guest",
            ])
            ->find($reserve_id);

            if ($reservation === NULL) {
                throw new \Exception("指定した予約情報が見つかりません｡");
            }

            // 以下指定した時間帯での重複ブッキングを検出する
            $duplicated_reservation = Reserve::where([
                ["service_id", "=", $get_data["service_id"]],
                ["id", "!=", $get_data["reserve_id"]],
                ["is_canceled", "=", Config("const.binary_type.off")],
                ["from_datetime", "<=", $reservation->to_datetime],
                ["to_datetime", ">", $reservation->from_datetime],
            ])
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

    /**
     * 特定の日付は､予約不可能にする(臨時休業など)
     *
     * @param ServiceRequest $request
     * @param integer $service_id
     * @return void
     */
    public function exclude_date(ServiceRequest $request, int $service_id)
    {
        try {

        } catch (\Throwable $e) {
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }
}
