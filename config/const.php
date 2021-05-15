<?php

return [


    // フラグ管理用
    "binary_type" => [
        "on" => 1,
        "off" => 0,
    ],

    // ストライプAPIキーの設定
    "stripe" => [
        "public_key" => env("stripe_public_key"),
        "private_key" => env("stripe_private_key")
    ],

    // レンタルする対象のサービス名
    "service_types" => [
        ["index" => 0   , "data" => "ー"],
        ["index" => 1   , "data" => "不動産"],
        ["index" => 2   , "data" => "高額商品"],
        ["index" => 3   , "data" => "自動車･バイク･自転車"],
        ["index" => 4   , "data" => "スタッフ・サービス"],
        ["index" => 100 , "data" => "その他"],
    ],
    "service_type_names" => [
        "none"              => 0, // 未設定
        "real_estate"       => 1, // 不動産
        "expensive_product" => 2, // 高額商品
        "vehicle"           => 3, // 乗り物全般
        "temp_agency"       => 4, // スタッフ･サービス
        "other"             => 100, // その他
    ],
    "image" => [
        // アップロード画像の最大規定サイズ
        "regulation_width" => 1024,
        "compression" => 100,
        // 同一ディレクトリ内に保存できるファイル数
        "directory_max" => 20000,
    ],

    // 予約可能な曜日一覧
    "reservable_days" => [
        ["index" => 0, "data" => "ー"],
        ["index" => 1, "data" => "日曜日"],
        ["index" => 2, "data" => "月曜日"],
        ["index" => 3, "data" => "火曜日"],
        ["index" => 4, "data" => "水曜日"],
        ["index" => 5, "data" => "木曜日"],
        ["index" => 6, "data" => "金曜日"],
        ["index" => 7, "data" => "土曜日"]
    ],
    "reservable_hours" => (function () {
        $hours = [];
        $hour_range = range(0, 23);
        foreach ($hour_range as $key => $value) {
            $hours[] = str_pad($value, 2, 0, STR_PAD_LEFT);
        }
        return $hours;
    })(),
    "reservable_minutes" => (function () {
        $hours = [];
        $hour_range = range(0, 59, 10);
        foreach ($hour_range as $key => $value) {
            $hours[] = str_pad($value, 2, 0, STR_PAD_LEFT);
        }
        return $hours;
    })(),
];
