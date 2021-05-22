<?php

namespace App\Models;

use App\Http\Requests\Api\ReserveRequest;
use App\Libraries\RandomToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Reserve extends Model
{

    use SoftDeletes;

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
        "token",
    ];

    protected $appends = [
        "from_datetime_ja",
        "to_datetime_ja",
        "created_at_ja",
        "updated_at_ja",
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, "service_id");
    }

    public function guest()
    {
        return $this->hasOne(Guest::class, "id", "guest_id");
    }

    // 予約開始日時を日本語フォーマット化
    public function getFromDateTimeJaAttribute()
    {
        return $this->from_datetime->format("Y年n月j日")."<br>".$this->from_datetime->format("H時i分s秒");
    }

    // 予約終了日時を日本語フォーマット化
    public function getToDateTimeJaAttribute()
    {
        return $this->to_datetime->format("Y年n月j日")."<br>".$this->to_datetime->format("H時i分s秒");
    }

    // レコード作成日時日本語フォーマット化
    public function getCreatedAtJaAttribute()
    {
        return $this->created_at->format("Y年n月j日")."<br>".$this->created_at->format("H時i分s秒");
    }

    // レコード更新日時日本語フォーマット化
    public function getUpdatedAtJaAttribute()
    {
        return $this->updated_at->format("Y年n月j日")."<br>".$this->updated_at->format("H時i分s秒");
    }

    /**
     * 指定した条件で新規スケジュールを取得する
     * 第二引数に有効なreserve_idが渡された場合は､予約スケジュールのアップデート作業とする
     *
     * @param ReserveRequest $request
     * @param integer $old_reserve_id
     * @return void
     */
    public static function makeNewReservation(ReserveRequest $request, int $old_reserve_id = NULL) : \App\Models\Reserve
    {
        try {
            // 当該トランザクションでのシーケンスを保持
            $last_insert_id = 0;
            // postデータの取得
            $post_data = $request->validated();

            // DB::enableQueryLog();
            DB::beginTransaction();
            // 対象の予約サービスのservicesテーブルの指定IDを行ロックする
            $service = Service::where([
                ["is_displayed", "=", Config("const.binary_type.on")],
                ["is_deleted", "=", Config("const.binary_type.off")],
                ["id", "=", $post_data["service_id"]]
            ])
            ->lockForUpdate();

            // 実行したSQL文
            logger()->debug($service->toSql(), $service->getBindings());

            $service = $service->get()->first();

            if ($service === NULL) {
                throw new \Exception("サービス情報の取得に失敗しました｡");
            }

            // リクエストされたスケジュールとの重複検証
            $reserve = Reserve::where([
                ["service_id", "=", $post_data["service_id"]],
                ["is_canceled", "=", Config("const.binary_type.off")],
                ["from_datetime", "<=", $post_data["to_datetime"]],
                ["to_datetime", ">", $post_data["from_datetime"]],
            ]);

            // 予約スケジュールのアップデートが目的の場合
            if ($old_reserve_id !== NULL) {
                $reserve = $reserve->where("id", "!=", $old_reserve_id);
            }

            // sqlの実行ログ
            logger()->debug($reserve->toSql(), $reserve->getBindings());

            $reserve = $reserve->get()->first();
            // 該当の期間に他の予約と重複する場合
            if ($reserve !== NULL) {
                throw new \Exception("リクエストされたスケジュールは既に予約が入っています｡");
            }

            // エンドユーザーの編集用トークン
            $random_token = RandomToken::MakeRandomToken(64);
            $temp = Reserve::where("token", $random_token)->get()->first();
            if ($temp !== NULL) {
                throw new \Exception("只今サーバーが混み合っています｡もう一度､送信して下さい｡");
            }

            $post_data["expired_at"] = date("Y-m-d H:i:s", time() + 60 * 30);
            $post_data["token"] = $random_token;

            // DBへの予約レコード問い合わせ
            $new_reservation = Reserve::create($post_data);

            // スケジュールが重複して登録されていないかを検証
            $last_insert_id = $new_reservation->id;
            $check_reservation = Reserve::where([
                ["service_id", "=", $post_data["service_id"]],
                // 自身のレコード未満を対象とする
                ["id", "<", $last_insert_id],
                ["is_canceled", "=", Config("const.binary_type.off")],
                ["from_datetime", "<=", $post_data["to_datetime"]],
                ["to_datetime", ">", $post_data["from_datetime"]],
            ]);

            // アップデートの場合
            if ($old_reserve_id > 0) {
                $check_reservation = $check_reservation->where("id", "!=", $old_reserve_id);
            }

            $check_reservation = $check_reservation->get()->first();

            if ($check_reservation !== NULL) {
                throw new \Exception("予約の確保ができませんでした｡別のスケジュールを指定して下さい｡");
            }

            // アップデートの場合､古い予約情報を削除する
            if ($old_reserve_id > 0) {
                $reserve = Reserve::find($old_reserve_id)->delete();
                if ($reserve !== true) {
                    throw new \Exception("予約スケジュールの変更ができませんでした｡");
                }
            }

            // DBのコミット
            DB::commit();

            return $new_reservation;
        } catch (\Throwable $e) {
            DB::rollback();
            logger()->error($e);
            throw new \Exception($e->getMessage());
        }
    }
}
