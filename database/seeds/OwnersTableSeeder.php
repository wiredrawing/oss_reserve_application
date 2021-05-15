<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OwnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table("owners")->insert([
            "owner_name" => "デフォルトオーナー",
            "owner_name_sort" => "デフォルトオーナー(よみがな)",
            "email" => "sample@sample.com",
            "phone_number" => "090-1234-5678",
            "description" => "description => seederから作成",
            "memo" => "memo => seederから作成",
            "administrator_id" => 1,
        ]);
    }
}
