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
    ]
];
