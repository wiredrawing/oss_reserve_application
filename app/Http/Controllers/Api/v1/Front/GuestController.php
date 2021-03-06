<?php

namespace App\Http\Controllers\Api\v1\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GuestRequest;
use App\Models\Guest;
use App\Libraries\RandomToken;
use Illuminate\Http\Request;
class GuestController extends Controller
{




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
            $token = RandomToken::MakeRandomToken(12);
            $post_data["token"] = $token;

            // このランダムトークンと重複していないかをDBで調べる
            $guest = Guest::where("token", $token)->get()->first();

            // トークンの生成に失敗
            if ($guest !== NULL) {
                throw new \Exception("只今､サーバーが混み合っています｡時間をおいてアクセスして下さい｡");
            }
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
}
