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
            // トランザクションの開始
            DB::beginTransaction();

            // postデータの取得
            $post_data = $request->validated();

            // リクエストされたスケジュールとの重複検証
            $reserve = Reserve::where("room_id", $post_data["room_id"])
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where(function ($query) use ($post_data) {
                $query->where(function ($query) use ($post_data) {
                    $query
                    ->where("is_canceled", Config("const.binary_type.off"))
                    ->where("from_datetime", "<", $post_data["to_datetime"])
                    ->where("to_datetime", ">", $post_data["from_datetime"]);
                });
                // ->orWhere(function($query) use ($post_data) {
                //    // 予約希望の終了日時日時を含むレコード
                //    $query
                //    ->where("is_canceled", Config("const.binary_type.off"))
                //    ->where("from_datetime", ">=", $post_data["from_datetime"])
                //    ->where("from_datetime", "<", $post_data["to_datetime"]);
                // });
            })
            ->get();

            // 該当の期間に他の予約と重複する場合
            if ($reserve->count() > 0) {
                throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡");
            }

            // エンドユーザーの編集用トークン
            $random_token = RandomToken::MakeRandomToken(64);
            $temp = Reserve::where("user_token", $random_token)->get()->first();
            if ($temp !== NULL) {
                throw new \Exception("只今サーバーが混み合っています｡もう一度､送信して下さい｡");
            }

            $post_data["expired_at"] = date("Y-m-d H:i:s", time() + 60 * 30);
            $post_data["user_token"] = $random_token;
            // DBへの予約レコード問い合わせ
            $reservation = Reserve::create($post_data);

            // スケジュールが重複して登録されていないかを検証
            $last_insert_id = $reservation->id;
            $check_reservation = Reserve::where("room_id", $post_data["room_id"])
            ->where("id", "!=", $last_insert_id)
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where(function ($query) use ($post_data) {
                $query->where(function ($query) use ($post_data) {
                    // 予約希望の開始日時を含むレコード
                    $query
                    ->where("is_canceled", Config("const.binary_type.off"))
                    ->where("from_datetime", "<", $post_data["to_datetime"])
                    ->where("to_datetime", ">", $post_data["from_datetime"]);
                });
                // ->orWhere(function($query) use ($post_data) {
                //    // 予約希望の終了日時日時を含むレコード
                //    $query
                //    ->where("from_datetime", ">=", $post_data["from_datetime"])
                //    ->where("from_datetime", "<", $post_data["to_datetime"]);
                // });
            })
            ->get();

            if ($check_reservation->count() > 0) {
                throw new \Exception("予約の確保ができませんでした｡別のスケジュールを指定して下さい｡");
            }

            // DBのコミット
            DB::commit();

            $response = [
                "status" => true,
                "data" => $reservation,
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            // DBのロールバック
            DB::rollback();
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
            // トランザクション開始
            DB::beginTransaction();

            $post_data = $request->validated();

            // print_r($post_data);

            $reservation = Reserve::where("room_id", $post_data["room_id"])
            ->where("id", "!=", $post_data["reserve_id"])
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where(function ($query) use ($post_data) {
                $query->where(function ($query) use ($post_data) {
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
                });
            })
            ->get();
            // print_r($reservation->toArray());
            if ($reservation->count() > 0) {
                throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡".__LINE__);
            }

            // 既存の予約情報
            // var_dump($reservation->count());
            // print_r($reservation->toArray());
            $reservation = Reserve::find($post_data["reserve_id"]);
            print_r($reservation->toArray());
            $result = $reservation->fill($post_data)->save();
            print_r($reservation->toArray());

            // スケジュールの更新に失敗
            if ($result !== true) {
                throw new \Exception("リクエストされたスケジュールの更新に失敗しました｡".__LINE__);
            }



            $check_reservation = Reserve::where("room_id", $post_data["room_id"])
            ->where("id", "!=", $post_data["reserve_id"])
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where(function ($query) use ($post_data) {
                $query->where(function ($query) use ($post_data) {
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
                });
            })
            ->get();
            // print_r($check_reservation->toArray());
            if ($check_reservation->count() > 0) {
                throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡".__LINE__);
            }

            DB::commit();
            $response = [
                "status" => true,
                "data" => $reservation,
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
            $post_data = $request->validated();

            $reservation = Reserve::where("id", $post_data["reserve_id"])
            ->where("user_token", $post_data["user_token"])
            ->get()
            ->first();

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
