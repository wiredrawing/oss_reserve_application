<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReserveRequest;
use App\Models\Reserve;
use Illuminate\Http\Request;

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
            $post_data = $request->validated();

            $reserve = Reserve::where("room_id", $post_data["room_id"])
            ->where(function ($query) use ($post_data) {
                // 予約希望の開始日時を含むレコード
                $query
                ->where("from_datetime", "<=", $post_data["from_datetime"])
                ->where("to_datetime", ">", $post_data["from_datetime"]);
            })
            ->orWhere(function($query) use ($post_data) {
               // 予約希望の終了日時日時を含むレコード
               $query
               ->where("from_datetime", ">=", $post_data["from_datetime"])
               ->where("from_datetime", "<", $post_data["to_datetime"]);
            })
            ->get();
            if ($reserve->count() > 0) {
                print_r("既に予約ずみ");
                exit();
            }

            $post_data["expired_at"] = date("Y-m-d H:i:s", time() + 60 * 30);
            $reserve = Reserve::create($post_data);

            $response = [
                "status" => true,
                "data" => $reserve,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            $response = [
                "status" => false,
                "data" => $e,
            ];
            return response()->json($response);
        }
    }
}
