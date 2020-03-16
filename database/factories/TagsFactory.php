<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\TagType::class, function (Faker $faker) {
    return [
        'name' => $faker->colorName,
    ];
});

$factory->define(\App\Models\Tags::class, function (Faker $faker) {
    return [
        'name' => $faker->colorName,
    ];
});
