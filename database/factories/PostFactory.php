<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Posts::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence[0],
        'content' => $faker->randomHtml(),
        'description' => $faker->shuffleString(),
        'seo_words' => implode(',', $faker->words()),
    ];
});
