<?php

namespace App\Models;

use App\Http\Requests\Api\ReserveRequest;
use App\Libraries\RandomToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Reserve extends Model
{

    protected $dates = [
        "from_datetime",
        "to_datetime",
    ];

    protected $fillable = [
        "from_datetime",
        "to_datetime",
        "service_id",
        "guest_id",
        "memo",
        "is_canceled",
        "is_confirmed",
        // 作成者
        "created_by",
        // 更新者
        "updated_by",
        // 仮予約期間
        "expired_at",
        // ユーザーの編集画面表示用
        "user_token",
    ];

    protected $appends = [
        "from_datetime_ja",
        "to_datetime_ja",
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, "service_id");
    }

    // 予約開始日時を日本語フォーマット化
    public function getFromDateTimeJaAttribute()
    {
        return $this->from_datetime->format("Y年n月j日 H時i分s秒");
    }

    // 予約終了日時を日本語フォーマット化
    public function getToDateTimeJaAttribute()
    {
        return $this->to_datetime->format("Y年n月j日 H時i分s秒");
    }

    /**
     * 指定した条件で新規スケジュールを取得する
     *
     *
     * @param ReserveRequest $request
     * @param integer $old_reserve_id
     * @return void
     */
    public static function makeNewReservation(ReserveRequest $request, int $old_reserve_id = NULL)
    {
        try {
            // DB::enableQueryLog();
            DB::beginTransaction();

            // 当該トランザクションでのシーケンスを保持
            $last_insert_id = 0;

            // postデータの取得
            $post_data = $request->validated();

            // リクエストされたスケジュールとの重複検証
            $reserve = Reserve::where("service_id", $post_data["service_id"])
            ->where("is_confirmed", Config("const.binary_type.on"))
            ->where("is_canceled", Config("const.binary_type.off"))
            // 以下予約不可能な条件
            ->where("from_datetime", "<=", $post_data["to_datetime"])
            ->where("to_datetime", ">", $post_data["from_datetime"]);

            // 予約スケジュールのアップデートが目的の場合
            if ($old_reserve_id !== NULL) {
                $reserve = $reserve->where("id", "!=", $old_reserve_id);
            }

            $reserve = $reserve->get()->first();

            // sqlの実行ログ
            // logger()->debug(dd($reserve->toSql(), $reserve->getBindings()));

            // 該当の期間に他の予約と重複する場合
            if ($reserve !== NULL) {
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
            $check_reservation = Reserve::where("service_id", $post_data["service_id"])
            // 自身のレコード未満を対象とする
            ->where("id", "<", $last_insert_id)
            ->where("is_canceled", Config("const.binary_type.on"))
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where("from_datetime", "<=", $post_data["to_datetime"])
            ->where("to_datetime", ">", $post_data["from_datetime"]);

            // アップデートの場合
            if ($old_reserve_id > 0) {
                $check_reservation = $check_reservation->where("id", "!=", $old_reserve_id);
            }

            $check_reservation = $check_reservation->get()->first();

            if ($check_reservation !== NULL) {
                throw new \Exception("予約の確保ができませんでした｡別のスケジュールを指定して下さい｡");
            }

            // DBのコミット
            DB::commit();

            // 指定した日時時間帯で重複があれば削除する
            $reservation = Reserve::where("service_id", $post_data["service_id"])
            ->where("id", "<", $last_insert_id)
            // 確定済みレコード
            ->where("is_confirmed", Config("const.binary_type.on"))
            // キャンセルされていないレコード
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where("from_datetime", "<=", $post_data["to_datetime"])
            ->where("to_datetime", ">", $post_data["from_datetime"])
            ->get()
            ->first();

            // 重複レコードのチェック
            if ($reservation !== NULL) {
                // 自身より先に､同じ時間帯で予約をしているレコードがある場合は削除する
                Reserve::find($last_insert_id)->deleted();
                throw new \Exception("予約の確保ができませんでした｡別のスケジュールを指定して下さい｡");
            }

            // 予約レコードを確定させる
            $reservation = Reserve::find($last_insert_id);
            $created_reservation = $reservation->fill([
                "is_confirmed" => Config("const.binary_type.on"),
            ])->save();

            // 予約レコードの確定に失敗した場合
            if ($created_reservation !== true) {
                throw new \Exception("指定した予約スケジュールの予約確定に失敗しました｡");
            }

            return $reservation;
        } catch (\Throwable $e) {
            DB::rollback();
            logger()->error($e);
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * 既存予約スケジュールのアップデート処理を行う
     *
     * @param ReserveRequest $request
     * @param integer $reserve_id
     * @return void
     */
    public static function updateExistingReservation(ReserveRequest $request, int $reserve_id)
    {
        try {
            // トランザクション開始
            DB::beginTransaction();

            $post_data = $request->validated();

            $reservation = Reserve::where("service_id", $post_data["service_id"])
            ->where("id", "!=", $reserve_id)
            ->where("is_confirmed", Config("const.binary_type.on"))
            ->where("is_canceled", Config("const.binary_type.off"))
            ->where("from_datetime", "<=", $post_data["to_datetime"])
            ->where("to_datetime", ">", $post_data["from_datetime"]);

            // sqlの実行ログ
            logger()->debug(dd($reservation->toSql(), $reservation->getBindings()));

            $reservation = $reservation->get()->first();

            // print_r($reservation->toArray());
            if ($reservation !== NULL) {
                throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡".__LINE__);
            }

            // 既存の予約情報を未確定状態にする
            $reservation = Reserve::find($reserve_id);
            $result = $reservation->fill([
                "is_confirmed" => Config("const.binary_type.off"),
            ])->save();

            // スケジュールの更新に失敗
            if ($result !== true) {
                throw new \Exception("現在の予約状態の一時未確定設定処理に失敗しました｡".__LINE__);
            }

            // 新規でリクエストした予約情報を取得する
            $updated_reservation = Reserve::makeNewReservation($request);

            DB::commit();

            // 更新された予約情報を取得する
            return $updated_reservation;
        } catch (\Throwable $e) {
            DB::rollback();
            logger()->error($e);
            throw new \Exception($e->getMessage());
        }
    }
}
