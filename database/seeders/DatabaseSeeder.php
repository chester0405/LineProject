<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('group')->insert([
            'title' => '預設群組',
            'is_default' => '1',
            'schedule_status' => '1',
            'record_status' => '0',
            'created_at' => now(),
            'updated_at' => now(),

        ]);
    }
}
