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

    Route::group([
        "prefix" => "reserve",
        "as" => "reserve.",
    ], function () {
        // 指定した期間の予約状況を取得する
        Route::get("/{reserve_id}/{from_datetime}/{to_datetime}", [
            "as" => "between",
            "uses" => "ReserveController@between",
        ]);
        // 指定したオブジェクトの予約情報を取得する
        Route::get("/{reserve_id}/{token}", [
            "as" => "detail",
            "uses" => "ReserveController@detail",
        ]);
        // 現在の全予約状況を取得
        Route::get("/list", [
            "as" => "list",
            "uses" => "ReserveController@list",
        ]);
    });

    Route::group([
        "prefix" => "room",
        "as" => "room.",
    ], function () {
        // 指定したroom_idのアクセス日時以降の予約状況を取得する
        Route::get("/{room_id}", [
            "as" => "detail",
            "uses" => "RoomController@detail",
        ]);
        // 現在予約可能な全オブジェクトを取得
        Route::get("/list", [
            "as" => "list",
            "uses" => "RoomController@list",
        ]);
    });
});
