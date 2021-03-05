<?php

namespace App\Libraries {



    class RandomToken
    {

        /**
         * 生成したいトークンの長さを指定する
         *
         * @param integer $length
         * @param boolean $exception
         * @return string
         */
        static public function MakeRandomToken (int $length = 64, string $prefix = "", bool $exception = true): string
        {

            // プレフィックスが指定されている場合
            if (strlen($prefix) > 0) {
                $length -= strlen($prefix);
            }

            // 生成するランダムトークン用バッファ
            $random_token = "";

            // 利用可能文字一覧を生成
            $characters = [];
            $characters = array_merge($characters, range("a", "z"), range("A", "Z"), range(0, 9));
            $characters[] = "-";
            $characters[] = "_";
            $characters[] = "!";
            $characters[] = ".";
            $characters[] = "*";
            // $characters[] = "!";
            // $characters[] = "$";
            // $characters[] = "^";
            $characters[] = "=";
            $token_size = count($characters);
            for ($i = 1; $i <= $length; $i++ ) {
                $offset = mt_rand(0, $token_size -1 );
                if (array_key_exists($offset, $characters) !== true) {
                    throw new \Exception ("Happened accessing invalid index of \$characters.");
                }
                $random_token .= $characters[$offset];
            }

            // 生成されたトークンが規定のバイト数かを検証
            if (strlen($random_token) !== $length) {
                if ($exception) {
                    throw new \Exception ("Failed making {$length} characters random token.");
                } else {
                    return (string)NULL;
                }
            }

            // 正常終了時
            return $prefix.$random_token;
        }
    }
}
