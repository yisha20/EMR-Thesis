<?php

use App\Service;
use App\User;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Checkup',
                'description' => 'some description'
            ],
            [
                'name' => 'Vaccine',
                'description' => 'some description'
            ],
            [
                'name' => 'Dressing',
                'description' => 'some description'
            ],
            [
                'name' => 'First Aid',
                'description' => 'some description'
            ],
            [
                'name' => 'Urinary Lab Test',
                'description' => 'some description'
            ],
            [
                'name' => 'Blood Test',
                'description' => 'some description'
            ],
            [
                'name' => 'Hematology Test',
                'description' => 'some description'
            ],
            [
                'name' => 'Follow Up Checkup',
                'description' => 'some description'
            ],
        ];

        foreach($services as $service) {
            Service::create([
                'name' => $service['name'],
                'description' => $service['description'],
                'added_by' => 1,
            ]);
        }
    }
}
