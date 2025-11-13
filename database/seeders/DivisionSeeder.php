<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Example: assuming you have companies with id 1, 2, 3
        DB::table('divisions')->insert([
            [
                'division_name' => 'Human Resources',
                'division_code' => 'HR',
                'company_id' => 1,
                'description' => 'Handles employee relations, payroll, recruitment, and training.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'division_name' => 'Finance',
                'division_code' => 'FIN',
                'company_id' => 1,
                'description' => 'Manages accounting, budgeting, and financial planning.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'division_name' => 'IT Department',
                'division_code' => 'IT',
                'company_id' => 2,
                'description' => 'Handles software development, network infrastructure, and IT support.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'division_name' => 'Marketing',
                'division_code' => 'MKT',
                'company_id' => 3,
                'description' => 'Responsible for branding, advertising, and market research.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
