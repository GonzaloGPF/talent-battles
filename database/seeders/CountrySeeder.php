<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::create([
            'name' => 'spain',
            'code' => 'es',
        ]);
        //        Country::create([
        //            'name' => 'United States',
        //            'code' => 'us',
        //        ]);
        //        Country::create([
        //            'name' => 'United Kingdom',
        //            'code' => 'gb',
        //        ]);
        //        Country::create([
        //            'name' => 'Portugal',
        //            'code' => 'pt',
        //        ]);
        //        Country::create([
        //            'name' => 'Italy',
        //            'code' => 'it',
        //        ]);
        //        Country::create([
        //            'name' => 'Germany',
        //            'code' => 'de',
        //        ]);
        //        Country::create([
        //            'name' => 'France',
        //            'code' => 'fr',
        //        ]);
        //        Country::create([
        //            'name' => 'Netherlands',
        //            'code' => 'nl',
        //        ]);
        //        Country::create([
        //            'name' => 'Switzerland',
        //            'code' => 'ch',
        //        ]);
        //        Country::create([
        //            'name' => 'Denmark',
        //            'code' => 'dk',
        //        ]);
    }
}
