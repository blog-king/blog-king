<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Post::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence[0],
        'content' => $faker->randomHtml(),
        'description' => $faker->shuffleString(),
        'thumbnail' => $faker->imageUrl(150, 150),
        'seo_words' => implode(',', $faker->words()),
    ];
});
