<?php

use App\Disease;
use Illuminate\Database\Seeder;

class DiseasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Disease::create([
            'name' => 'Allergies',
        ]);

        Disease::create([
            'name' => 'Amoebiasis',
        ]);

        Disease::create([
            'name' => 'Anemia',
        ]);

        Disease::create([
            'name' => 'Arthritis',
        ]);

        Disease::create([
            'name' => 'Back and Joint Pains',
        ]);

        Disease::create([
            'name' => 'Bone fracture',
        ]);

        Disease::create([
            'name' => 'Breast mass/lump',
        ]);

        Disease::create([
            'name' => 'Chest pains',
        ]);

        Disease::create([
            'name' => 'Chicken pox',
        ]);

        Disease::create([
            'name' => 'Diabetes Mellitus',
        ]);

        Disease::create([
            'name' => 'Epilepsy',
        ]);

        Disease::create([
            'name' => 'Eye or Ear problem',
        ]);

        Disease::create([
            'name' => 'Gallbladder Stone',
        ]);

        Disease::create([
            'name' => 'Goiter',
        ]);

        Disease::create([
            'name' => 'Gout',
        ]);

        Disease::create([
            'name' => 'Hemorrhoids',
        ]);

        Disease::create([
            'name' => 'Hepatitis: A / B / C',
        ]);

        Disease::create([
            'name' => 'Hypertension',
        ]);

        Disease::create([
            'name' => 'Kidney/Bladder Stones',
        ]);

        Disease::create([
            'name' => 'Loss of Consciousness',
        ]);

        Disease::create([
            'name' => 'Measles',
        ]);

        Disease::create([
            'name' => 'Mumps',
        ]);

        Disease::create([
            'name' => 'Pneumonia',
        ]);

        Disease::create([
            'name' => 'Prostate Problems',
        ]);
        
        Disease::create([
            'name' => 'Seizure',
        ]);

        Disease::create([
            'name' => 'Sinusitis/Aliergic rhinitis',
        ]);

        Disease::create([
            'name' => 'Skin Disorders',
        ]);

        Disease::create([
            'name' => 'STI/HIV',
        ]);

        Disease::create([
            'name' => 'Stroke',
        ]);

        Disease::create([
            'name' => 'Surgery/Injury',
        ]);

        Disease::create([
            'name' => 'Thyroid Problems',
        ]);

        Disease::create([
            'name' => 'Tonsillitis',
        ]);

        Disease::create([
            'name' => 'Tuberculosis',
        ]);

        Disease::create([
            'name' => 'UTI',
        ]);
    }
}
