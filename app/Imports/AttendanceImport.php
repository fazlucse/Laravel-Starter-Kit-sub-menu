<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class AttendanceImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
//       dd($row);
        $baseDate = $this->parseTime($row['date']);
        if (!$baseDate) return null;
        $dateString = $baseDate->format('Y-m-d');
        $empInTime = $this->parseOnlyTime($row['in_time']);
        $empOutTime = $this->parseOnlyTime($row['out_time']);
        $employee = Employee::with('person')
            ->where('person_id', $row['employee_id'])
            ->where('employee_status', 'Active')
            ->first();
        if (!$employee) return null;
        return Attendance::updateOrCreate(
            [
                'contact_id' => $employee['person_id'],
                'add_time'    => $dateString,
            ],
            [
                'contact_id'  => $employee->person_id??0,
                'employee_id'  => $employee->employee_id??'',
                'emp_name'  => $employee->person_name??'',
                'designation'  => $employee->designation_id??0,
                'designation_name'  => $employee->designation_name??'',
                'operational_office'  => $employee->company_id??0,
                'operational_office_name'  => $employee->company_name??'',
                'department'  => $employee->department_id??0,
                'department_name'  => $employee->department_name??'',
                'office_in_time'  => $employee->office_in_time??'',
                'office_out_time'  => $employee->office_out_time??'',
                'division'  => $employee->division_id??0,
                'division_name'  => $employee->division_name??'',

                'emp_in_time'      => $empInTime,
                'emp_out_time'     => $empOutTime,

                'entry_from'       => 'Excel',
                'is_manual'        => true,
                'editby'           => auth()->id(),
                'edit_time'        => now(),
                'addby'            => auth()->id(),
            ]
        );
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required',
            'date'        => 'required',
            'in_time'     => 'required',
        ];
    }

    /**
     * Helper to merge a date string with a time string/object
     */
    private function combineDateAndTime($date, $timeValue)
    {
        if (!$timeValue) return null;

        try {
            // If it's an Excel time (decimal), convert it
            if (is_numeric($timeValue)) {
                $time = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($timeValue))->format('H:i:s');
            } else {
                $time = Carbon::parse($timeValue)->format('H:i:s');
            }

            return Carbon::createFromFormat('Y-m-d H:i:s', "$date $time");
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime($value)
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Parse only time from Excel or string value
     */
    private function parseOnlyTime($value)
    {
        if (!$value) return null;

        try {
            // If Excel numeric time
            if (is_numeric($value)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                )->format('H:i:s');
            }

            // If normal string time
            return Carbon::parse($value)->format('H:i:s');

        } catch (\Exception $e) {
            return null;
        }
    }

}
