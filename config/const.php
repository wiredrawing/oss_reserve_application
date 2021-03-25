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
    ]
];
