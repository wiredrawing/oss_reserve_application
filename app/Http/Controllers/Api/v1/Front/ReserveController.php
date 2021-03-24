<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReserveRequest;
use App\Models\Reserve;
use App\Libraries\RandomToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReserveController extends Controller
{


    /**
     * 新規予約の取得
     *
     * @param ReserveRequest $request
     * @return void
     */
    public function create (ReserveRequest $request)
    {
        try {
            // 新規予約レコードを取得する
            $reservation = Reserve::makeNewReservation($request);

            $response = [
                "status" => true,
                "data" => $reservation,
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
     * 指定された予約情報をエンドユーザーが更新する
     *
     * @param ReserveRequest $request
     * @param integer $reserve_id
     * @param string $token
     * @return void
     */
    public function update(ReserveRequest $request, int $reserve_id, string $token = "")
    {
        try {
            $updated_reservation = Reserve::updateExistingReservation($request);
            // // トランザクション開始
            // DB::beginTransaction();
            // $post_data = $request->validated();


            // $reservation = Reserve::where("service_id", $post_data["service_id"])
            // ->where("id", "<", $post_data["reserve_id"])
            // ->where("is_confirmed", Config("const.binary_type.on"))
            // ->where("is_canceled", Config("const.binary_type.off"))
            // ->where(function ($query) use ($post_data) {
            //     $query
            //     ->where("from_datetime", "<=", $post_data["to_datetime"])
            //     ->where("to_datetime", ">"< $post_data["from_datetime"]);
            // })
            // ->get();
            // // print_r($reservation->toArray());
            // if ($reservation->count() > 0) {
            //     throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡".__LINE__);
            // }

            // // 既存の予約情報
            // $reservation = Reserve::find($post_data["reserve_id"]);
            // $result = $reservation->fill($post_data)->save();

            // // スケジュールの更新に失敗
            // if ($result !== true) {
            //     throw new \Exception("リクエストされたスケジュールの更新に失敗しました｡".__LINE__);
            // }



            // $check_reservation = Reserve::where("service_id", $post_data["service_id"])
            // ->where("id", "<", $post_data["reserve_id"])
            // ->where("is_confirmed", Config("const.binary_type.on"))
            // ->where("is_canceled", Config("const.binary_type.off"))
            // ->where(function ($query) use ($post_data) {
            //     $query
            //     ->where("from_datetime", "<=", $post_data["to_datetime"])
            //     ->where("to_datetime", ">"< $post_data["from_datetime"]);
            // })
            // ->get();
            // // print_r($check_reservation->toArray());
            // if ($check_reservation->count() > 0) {
            //     throw new \Exception("スケジュールの更新に失敗しました｡".__LINE__);
            // }

            // DB::commit();
            $response = [
                "status" => true,
                "data" => $updated_reservation,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollback();
            $response = [
                "status" => false,
                "data" => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }



    /**
     * 指定された予約情報を取得する
     *
     * @param ReserveRequest $request
     * @param integer $reserve_id
     * @param string $token
     * @return void
     */
    public function detail(ReserveRequest $request, int $reserve_id, string $token = "")
    {
        try {
            // バリデーションデータ
            $validated_data = $request->validated();

            $reservation = Reserve::with([
                "service",
            ])
            ->where("is_confirmed", Config("const.binary_type.on"))
            ->find($validated_data["reserve_id"]);

            if ($reservation === NULL) {
                throw new \Exception("予約情報を取得できません｡");
            }
            $response = [
                "status" => true,
                "data" => $reservation,
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
