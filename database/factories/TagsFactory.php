<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Tags::class, function (Faker $faker) {
    return [
        'name' => $faker->colorName,
    ];
});
