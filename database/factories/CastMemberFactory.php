<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Genre;
use Faker\Generator as Faker;

$factory->define(\App\Models\CastMember::class, function (Faker $faker) {
    return [
        'name' => $faker->lastName,
        'type' => array_rand(\App\Models\CastMember::getCastMembers())
    ];
});
