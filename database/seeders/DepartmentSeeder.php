<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Example: assuming divisions with id 1,2,3 and companies with id 1,2,3 exist
        DB::table('departments')->insert([
            [
                'department_name' => 'Recruitment',
                'division_id' => 1,   // HR division
                'company_id' => 1,
                'description' => 'Handles recruitment and onboarding of new employees.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'department_name' => 'Payroll & Benefits',
                'division_id' => 1,   // HR division
                'company_id' => 1,
                'description' => 'Manages payroll, benefits, and compensation.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'department_name' => 'Accounting',
                'division_id' => 2,   // Finance division
                'company_id' => 1,
                'description' => 'Handles accounts payable and receivable.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'department_name' => 'IT Support',
                'division_id' => 3,   // IT division
                'company_id' => 2,
                'description' => 'Provides IT support and maintenance.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'department_name' => 'Digital Marketing',
                'division_id' => 4,   // Marketing division
                'company_id' => 3,
                'description' => 'Handles online marketing campaigns.',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
