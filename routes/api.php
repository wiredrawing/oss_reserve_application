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

    // ユーティリティー情報を取得する
    Route::group([
        "prefix" => "utility",
        "as" => "utility.",
    ], function () {
        Route::get("/service_type", [
            "as" => "service_type",
            "uses" => "UtilityController@service_type"
        ]);
        Route::get("/reservable_days", [
            "as" => "reservable_days",
            "uses" => "UtilityController@reservable_days",
        ]);
        Route::get("/reservable_hours", [
            "as" => "reservable_hours",
            "uses" => "UtilityController@reservable_hours",
        ]);
        Route::get("/reservable_minutes", [
            "as" => "reservable_minutes",
            "uses" => "UtilityController@reservable_minutes",
        ]);
    });

    // オーナー情報関連
    Route::group([
        "prefix" => "owner",
        "as" => "owner.",
    ], function () {
        Route::post("/create", [
            "as" => "create",
            "uses" => "OwnerController@create",
        ]);
        Route::get("/{owner_id}", [
            "as" => "detail",
            "uses" => "OwnerController@detail",
        ]);
        Route::post("/{owner_id}/update/", [
            "as" => "update",
            "uses" => "OwnerController@update",
        ]);
        Route::get("/", [
            "as" => "list",
            "uses" => "OwnerController@list"
        ]);
    });

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
        Route::post("/check", [
            "as" => "check",
            "uses" => "GuestController@check",
        ]);
        // 指定したゲスト情報を更新する
        Route::post("/{guest_id}/update", [
            "as" => "update",
            "uses" => "GuestController@update",
        ]);
        // 指定したゲスト情報を取得
        Route::get("/{guest_id}", [
            "as" => "detail",
            "uses" => "GuestController@detail",
        ]);
        // 登録中の全ゲストデータを取得
        Route::get("/", [
            "as" => "index",
            "uses" => "GuestController@index",
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
        Route::post("/{reserve_id}/{token}/update", [
            "as" => "update",
            "uses" => "ReserveController@update",
        ]);
        // 指定したサービスの予約情報を取得する
        Route::get("/{reserve_id}/{token}", [
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
        // サービス情報の入力内容の検証API
        Route::post("/check", [
            "as" => "check",
            "uses" => "ServiceController@check"
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
        // 指定したサービス情報の詳細を取得
        Route::get("/detail/{service_id}", [
            "as" => "detail",
            "uses" => "ServiceController@detail"
        ]);
        // 指定した日時を臨時休業とする
        Route::post("/exclude_date", [
            "as" => "exclude_date",
            "uses" => "ServiceController@exclude_date",
        ]);
    });

    Route::group([
        "prefix" => "image",
        "as" => "image.",
    ], function () {
        // 画像一覧を取得する
        Route::get("/count", [
            "as" => "count",
            "uses" => "ImageController@count",
        ]);
        Route::get("/list", [
            "as" => "list",
            "uses" => "ImageController@list",
        ]);
        Route::get("/list/{offset}", [
            "as" => "list",
            "uses" => "ImageController@list",
        ]);
        Route::get("/list/{offset}/{limit}", [
            "as" => "list",
            "uses" => "ImageController@list",
        ]);

        Route::group([
            "prefix" => "owner",
            "as" => "owner."
        ], function () {
            // owner情報に画像を紐付ける
            Route::post("/", [
                "as" => "add",
                "uses" => "ImageController@owner",
            ]);
            // ownerに紐付いた画像を解除する
            Route::post("/delete", [
                "as" => "delete",
                "uses" => "ImageController@owner_delete"
            ]);
            Route::get("/images/{owner_id}", [
                "as" => "images",
                "uses" => "ImageController@owner_images",
            ]);
        });


        Route::group([
            "prefix" => "service",
            "as" => "service.",
        ], function () {
            // service情報に画像を紐付ける
            Route::post("/", [
                "as" => "add",
                "uses" => "ImageController@service",
            ]);
            // serviceに紐付いた画像を解除する
            Route::post("/delete", [
                "as" => "delete",
                "uses" => "ImageController@service_delete"
            ]);
            Route::get("/images/{service_id}", [
                "as" => "images",
                "uses" => "ImageController@service_images",
            ]);
        });


        Route::get("/show/{image_id}/{token}", [
            "as" => "show",
            "uses" => "ImageController@show",
        ]);
        Route::post("/good/{image_id}", [
            "as" => "good",
            "uses" => "ImageController@good",
        ]);
        Route::post("/delete/{image_id}", [
            "as" => "delete",
            "uses" => "ImageController@delete"
        ]);
        Route::post("/upload", [
            "as" => "upload",
            "uses" => "ImageController@upload",
        ]);
    });
});
