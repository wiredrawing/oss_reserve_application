<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GuestRequest;
use App\Models\Guest;
use App\Libraries\RandomToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class GuestController extends Controller
{


    /**
     * 全ゲスト情報を取得する
     *
     * @param GuestRequest $request
     * @return void
     */
    public function index(GuestRequest $request)
    {
        try {
            $guests = Guest::with([
                "reserves"
            ])->where([
                ["is_displayed", "=", Config("const.binary_type.on")],
                ["is_deleted", "=", Config("const.binary_type.off")]
            ])
            ->get();

            // レスポンス返却
            $response = [
                "status" => true,
                "data" => $guests,
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


    /**
     * 新規ゲスト情報を登録する
     *
     * @param GuestRequest $request
     * @return void
     */
    public function create(GuestRequest $request)
    {
        try {
            $post_data = $request->validated();

            // アクセス用のランダムトークンを生成
            $token = RandomToken::MakeRandomToken(64);

            // このランダムトークンと重複していないかをDBで調べる
            $guest = Guest::where("token", $token)->get()->first();

            // トークンの生成に失敗
            if ($guest !== NULL) {
                throw new \Exception("只今､サーバーが混み合っています｡時間をおいてアクセスして下さい｡");
            }

            $post_data["token"] = $token;
            $guest = Guest::create($post_data);

            // レスポンスを整形
            $response = [
                "status" => true,
                "data" => $guest
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



    /**
     * 指定したguest_idのゲスト情報を更新する
     *
     * @param GuestRequest $request
     * @param integer $guest_id
     * @return void
     */
    public function update (GuestRequest $request, int $guest_id)
    {
        try {

            $post_data = $request->validated();

            DB::beginTransaction();

            // 専有ロックを行ロックする
            $guest = Guest::lockForUpdate()
            ->where([
                ["id", "=", $guest_id],
                ["is_displayed", "=", Config("const.binary_type.on")],
                ["is_deleted", "=", Config("const.binary_type.off")],
            ])
            ->get()
            ->first();

            // ゲスト情報をアップデート
            $result = $guest->fill($post_data)->save();

            if ($result !== true) {
                throw new \Exception("ゲスト情報のアップデートに失敗しました｡");
            }

            DB::commit();

            // レスポンスを整形
            $response = [
                "status" => true,
                "data" => $guest
            ];
            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollback();
            $response = [
                "status" => false,
                "data" => $e,
            ];
            return response()->json($response);
        }
    }


    /**
     * 指定したゲスト情報を取得する
     *
     * @param GuestRequest $request
     * @param integer $guest_id
     * @return void
     */
    public function detail(GuestRequest $request, int $guest_id)
    {
        try {
            $guest = Guest::where("is_displayed", Config("const.binary_type.on"))
            ->where("is_deleted", Config("const.binary_type.off"))
            ->find($guest_id);

            // ゲスト情報の存在チェック
            if ($guest === NULL) {
                throw new \Exception("指定したゲスト情報が見つかりません｡");
            }

            $response = [
                "status" => true,
                "data" => $guest,
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
