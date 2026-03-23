<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        // Chunk the people table to handle large data (49k+ records)
        DB::table('people')->orderBy('id')->chunk(10, function ($people) {
            $employeeData = [];

            foreach ($people as $person) {
                $employeeData[] = [
                    'person_id'         => $person->id,
                    'person_name'       => $person->name,
                    'company_id'        => 1, // Assuming a default company
                    'company_name'      => $person->company ?? 'Your Tech Corp',
                    'fin_com_id'        => 1,
                    'department_id'     => rand(1, 5), // Random Dept ID for testing
                    'department_name'   => 'Information Technology',
                    'designation_id'    => rand(1, 10),
                    'designation_name'  => $person->designation ?? 'Software Engineer',
                    'employee_code'     => 'EMP-' . str_pad($person->id, 5, '0', STR_PAD_LEFT),
                    'employee_id'       => 'ID-' . $person->id,
                    'joining_date'      => Carbon::now()->subMonths(rand(1, 60))->format('Y-m-d'),
                    'effective_date'    => Carbon::now()->subMonths(rand(1, 60))->format('Y-m-d'),
                    'employment_type'   => 'Permanent',
                    'official_email'    => $person->email,
                    'official_phone'    => $person->phone,
                    'office_in_time'    => '09:00:00',
                    'office_out_time'   => '18:00:00',
                    'employee_status'   => 'Active',
                    'is_tax_deduction'   => 0,
                    'is_salary_stop'    => 0,
                    'gross_salary'      => $gross = rand(30000, 150000),
                    'basic_salary'      => $gross * 0.5,
                    'house_rent_allowance' => $gross * 0.3,
                    'medical_allowance' => $gross * 0.1,
                    'transport_allowance' => $gross * 0.1,
                    'total_salary'      => $gross,
                    'currency'          => 'BDT',
                    'late_time'         => 15,
                    'gender'            => in_array($person->gender, ['Male', 'Female']) ? $person->gender : 'Male',
                    'blood_group'       => 'B+',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            DB::table('employees')->insert($employeeData);
        });
    }
}
