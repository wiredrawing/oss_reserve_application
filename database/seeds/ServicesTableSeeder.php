<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("services")->insert([
            "service_name" => "予約枠1",
            "owner_id" => 1,
            "memo" => "メモ",
            "capacity" => 1,
            "price_per_hour" => 2500,
            "service_type" => 1,
            "is_displayed" => 1,
            "is_deleted" => 0,
            "created_by" => 0,
            "updated_by" => 0,
        ]);
    }
}
