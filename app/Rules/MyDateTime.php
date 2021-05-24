<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MyDateTime implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $dateTime = \DateTime::createFromFormat("Y-n-j H:i:s", $value);
        // 入力された､日付が正しくタイムスタンプ化されない場合
        if ($dateTime === false) {
            return false;
        }

        $converted = (new \DateTime())->setTimestamp($dateTime->getTimestamp())->format("Y-n-j H:i:s");
        if ($value !== $converted) {
            return false;
        }
        // 日付 -> タイムスタンプ -> 日付 が正しくフォーマットできた場合
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeが正しい日付情報ではありません｡';
    }
}
