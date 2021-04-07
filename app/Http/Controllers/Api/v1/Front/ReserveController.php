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
            $updated_reservation = Reserve::makeNewReservation($request, $reserve_id);
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
                "guest",
            ])
            ->where([
                ["is_canceled", "=", Config("const.binary_type.off")],
                ["user_token", "=", $token],
            ])
            ->find($reserve_id);

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


    /**
     * 現在､確定されている予約情報一覧
     *
     * @param ReserveRequest $request
     * @return void
     */
    public function list(ReserveRequest $request)
    {
        try {
            $reserves = Reserve::with([
                "guest",
                "service",
            ])->where([
                ["is_canceled", "=", Config("const.binary_type.off")],
            ])
            ->get();
            $response = [
                "status" => true,
                "data" => $reserves,
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
