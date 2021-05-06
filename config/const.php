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
        1 => "不動産",
        2 => "高額商品",
        3 => "自動車･バイク･自転車",
        4 => "スタッフ・サービス",
        100 => "その他"
    ],

    "image" => [
        // アップロード画像の最大規定サイズ
        "regulation_width" => 1024,
        "compression" => 100,
        // 同一ディレクトリ内に保存できるファイル数
        "directory_max" => 20000,
    ],

    // 予約可能な曜日一覧
    "reservable_dates" => [
        ["index" => "0", "data" => "日曜日"],
        ["index" => "1", "data" => "月曜日"],
        ["index" => "2", "data" => "火曜日"],
        ["index" => "3", "data" => "水曜日"],
        ["index" => "4", "data" => "木曜日"],
        ["index" => "5", "data" => "金曜日"],
        ["index" => "6", "data" => "土曜日"]
    ],
    "hours" => (function () {
        $hour_range = range(0, 23);
        foreach ($hour_range as $key => $value) {
            var_dump($value);
        }
        return [];
    })(),
];
