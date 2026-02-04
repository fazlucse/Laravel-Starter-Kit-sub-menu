<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;

class PersonSeeder extends Seeder
{
    public function run()
    {
        $chunkSize = 10; // insert in chunks to avoid memory issues
        $total = 50; // 10 lakh

        for ($i = 0; $i < $total / $chunkSize; $i++) {
            Person::factory()->count($chunkSize)->create();
            echo "Inserted " . (($i + 1) * $chunkSize) . " records\n";
        }
    }
}
