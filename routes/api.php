<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    "namespace" => "Api\\v1\\Front",
    "prefix" => "v1/front",
    "as" => "api.front.",
], function () {

    // ゲスト情報
    Route::group([
        "prefix" => "guest",
        "as" => "guest.",
    ], function () {
        // ゲスト情報登録用バリデーター
        Route::post("/validate", [
            "as" => "validate",
            "uses" => "GuestController@validate",
        ]);
        // ゲスト情報新規登録
        Route::post("/create", [
            "as" => "create",
            "uses" => "GuestController@create",
        ]);
        // 指定したゲスト情報を更新する
        Route::post("/{guest_id}/update", [
            "as" => "update",
            "uses" => "GuestController@update",
        ]);
        // 指定したゲスト情報を取得
        Route::post("/{guest_id}", [
            "as" => "detail",
            "uses" => "GuestController@detail",
        ]);
    });

    Route::group([
        "prefix" => "reserve",
        "as" => "reserve.",
    ], function () {
        // 指定した期間の予約状況を取得する
        Route::get("/{reserve_id}/{from_datetime}/{to_datetime}", [
            "as" => "between",
            "uses" => "ReserveController@between",
        ]);
        // 指定したサービスの予約情報を更新する
        Route::post("/{reserve_id}/{user_token}/update", [
            "as" => "update",
            "uses" => "ReserveController@update",
        ]);
        // 指定したサービスの予約情報を取得する
        Route::get("/{reserve_id}/{user_token}", [
            "as" => "detail",
            "uses" => "ReserveController@detail",
        ]);
        // 現在の全予約状況を取得
        Route::get("/list", [
            "as" => "list",
            "uses" => "ReserveController@list",
        ]);
        // 予約登録処理
        Route::post("/create", [
            "as" => "create",
            "uses" => "ReserveController@create",
        ]);
        // 予約送信前のバリデーション処理
        Route::post("/validate", [
            "as" => "validate",
            "uses" => "ReserveController@validate",
        ]);
    });

    Route::group([
        "prefix" => "service",
        "as" => "service.",
    ], function () {
        // 新規サービス情報の登録
        Route::post("/create", [
            "as" => "create",
            "uses" => "ServiceController@create"
        ]);
        // 既存サービスの更新処理
        Route::post("/update/{service_id}", [
            "as" => "update",
            "uses" => "ServiceController@update"
        ]);
        // 現在登録中の利用可能なサービス
        Route::get("/list", [
            "as" => "list",
            "uses" => "ServiceController@list"
        ]);
        Route::get("/schedule/{service_id}/detail", [
            "as" => "detail",
            "uses" => "ServiceController@detail",
        ]);
        // 指定したroom_idのアクセス日時以降の予約状況を取得する
        Route::get("/schedule/{service_id}", [
            "as" => "schedule",
            "uses" => "ServiceController@schedule",
        ]);
        // ダブルブッキングチェック
        Route::get("/duplication_check/{service_id}/{reserve_id}", [
            "as" => "duplication_check",
            "uses" => "ServiceController@duplication_check",
        ]);
    });
});
