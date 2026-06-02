<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'Anne',
            'middle_name' => 'Medieval',
            'last_name' => 'Abdulazis',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'civil_status' => 'Single',
            'age' => 20,
            'birthdate' => today(),
            'present_address' => 'Earth',
            'gender' => 'Female',
            'role_id' => 1,
            'phone_number' => 9167784412,
            'license_number' => '123456',
        ]);

        User::create([
            'first_name' => 'Doky Carl',
            'middle_name' => 'Medieval',
            'last_name' => 'Schawrznerger',
            'email' => 'doctor@doctor.com',
            'password' => bcrypt('password'),
            'civil_status' => 'Single',
            'age' => 20,
            'birthdate' => today(),
            'present_address' => 'Earth',
            'gender' => 'Female',
            'role_id' => 2,
            'phone_number' => 9167784412,
            'license_number' => '123456',
        ]);

        User::create([
            'first_name' => 'Nars',
            'middle_name' => 'Medieval',
            'last_name' => 'Musky',
            'email' => 'nurse@nurse.com',
            'password' => bcrypt('password'),
            'civil_status' => 'Single',
            'age' => 20,
            'birthdate' => today(),
            'present_address' => 'Earth',
            'gender' => 'Female',
            'role_id' => 3,
            'phone_number' => 9167784412,
            'license_number' => '123456',
        ]);
    }
}
