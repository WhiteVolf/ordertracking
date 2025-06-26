<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nationality;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        $nationalities = ['Ukrainian', 'Polish', 'German'];

        foreach ($nationalities as $name) {
            Nationality::firstOrCreate(['name' => $name]);
        }
    }
}
