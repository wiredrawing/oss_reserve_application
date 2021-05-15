<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AdministratorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table("administrators")->insert([
            "login_id" => "dummy",
            "password" => password_hash("AAAaaa123", PASSWORD_DEFAULT),
            "email" => "dummy@gmail.com",
            "phone_number" => "08012345678",
            "administrator_name" => "管理者名",
            "memo" => "メモ帳",
            "last_login" => (new DateTime())->format("Y-m-d H:i:s"),
        ]);
    }
}
