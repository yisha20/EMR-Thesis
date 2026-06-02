<?php

use App\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'Administrator',
        ]);

        Role::create([
            'name' => 'Doctor',
        ]);

        Role::create([
            'name' => 'Nurse',
        ]);

        Role::create([
            'name' => 'OPD/Dependent',
        ]);
    }
}
