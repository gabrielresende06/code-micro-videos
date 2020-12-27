<?php

use Illuminate\Database\Seeder;

class VideosSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        factory(\App\Models\Video::class, 100)->create();
    }
}
