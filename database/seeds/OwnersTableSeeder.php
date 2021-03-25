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
        ]);
    }
}
