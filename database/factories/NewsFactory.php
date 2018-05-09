<?php

use App\News;
use Faker\Generator as Faker;

$factory->define(News::class, function (Faker $faker) {
    return [
        'title' => $faker->words(3, true),
        'body' => $faker->paragraph
    ];
});
