<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Patient;
use Faker\Generator as Faker;

$factory->define(Patient::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'middle_name' => $faker->lastName,
        'last_name' => $faker->lastName,
        'gender' => 'Male',
        'phone_number' => $faker->phoneNumber,
        'college_department' => 'Some Department',
        'type' => 'Some Type',
        'status' => 'Some status',
        'home_address' => $faker->address,
        'present_address' => $faker->address,
        'age' => $faker->numberBetween($min = 12, $max = 55),
        'birthdate' => now(),
        'added_by' => $faker->numberBetween($min = 1, $max = User::count()),
        'updated_by' => $faker->numberBetween($min = 1, $max = User::count()),
    ];
});
