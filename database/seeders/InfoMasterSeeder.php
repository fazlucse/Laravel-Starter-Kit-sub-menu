<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InfoMaster;
use Carbon\Carbon;

class InfoMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 🌍 Countries
        $countries = [
            ['name' => 'Bangladesh', 'code' => 'BD', 'description' => 'Country in South Asia'],
            ['name' => 'India', 'code' => 'IN', 'description' => 'Country in South Asia'],
            ['name' => 'Nepal', 'code' => 'NP', 'description' => 'Landlocked country in South Asia'],
            ['name' => 'Bhutan', 'code' => 'BT', 'description' => 'Mountainous kingdom in the Himalayas'],
            ['name' => 'Sri Lanka', 'code' => 'LK', 'description' => 'Island nation in South Asia'],
            ['name' => 'Pakistan', 'code' => 'PK', 'description' => 'Country in South Asia'],
            ['name' => 'United States', 'code' => 'US', 'description' => 'Country in North America'],
            ['name' => 'Canada', 'code' => 'CA', 'description' => 'Country in North America'],
            ['name' => 'United Kingdom', 'code' => 'UK', 'description' => 'Country in Europe'],
            ['name' => 'Australia', 'code' => 'AU', 'description' => 'Country and continent in Oceania'],
        ];

        $countryIds = [];
        foreach ($countries as $country) {
            $countryIds[$country['name']] = InfoMaster::create([
                'type' => 'country',
                'name' => $country['name'],
                'code' => $country['code']??"",
                'description' => $country['description'],
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])->id;
        }

        // 🏙️ Cities (Bangladesh + some other countries)
        $cities = [
            // 🇧🇩 Bangladesh cities
            ['name' => 'Dhaka', 'code' => 'DAC', 'country' => 'Bangladesh', 'description' => 'Capital of Bangladesh'],
            ['name' => 'Chittagong', 'code' => 'CGP', 'country' => 'Bangladesh', 'description' => 'Port city in Bangladesh'],
            ['name' => 'Sylhet', 'code' => 'ZYL', 'country' => 'Bangladesh', 'description' => 'Tea city of Bangladesh'],
            ['name' => 'Khulna', 'code' => 'KHL', 'country' => 'Bangladesh', 'description' => 'City in southwest Bangladesh'],
            ['name' => 'Rajshahi', 'code' => 'RJS', 'country' => 'Bangladesh', 'description' => 'City in northwest Bangladesh'],
            ['name' => 'Barisal', 'code' => 'BSL', 'country' => 'Bangladesh', 'description' => 'City in southern Bangladesh'],
            ['name' => 'Rangpur', 'code' => 'RNG', 'country' => 'Bangladesh', 'description' => 'City in northern Bangladesh'],
            ['name' => 'Mymensingh', 'code' => 'MYM', 'country' => 'Bangladesh', 'description' => 'City in central Bangladesh'],
            ['name' => 'Comilla', 'code' => 'CML', 'country' => 'Bangladesh', 'description' => 'City in eastern Bangladesh'],
            ['name' => 'Narsingdi', 'code' => 'NSD', 'country' => 'Bangladesh', 'description' => 'City near Dhaka'],
            ['name' => 'Tangail', 'code' => 'TGL', 'country' => 'Bangladesh', 'description' => 'City in central Bangladesh'],
            ['name' => 'Gazipur', 'code' => 'GZP', 'country' => 'Bangladesh', 'description' => 'City near Dhaka'],
            ['name' => 'Feni', 'code' => 'FEN', 'country' => 'Bangladesh', 'description' => 'City in southeastern Bangladesh'],
            ['name' => 'Cox\'s Bazar', 'code' => 'CXB', 'country' => 'Bangladesh', 'description' => 'Famous beach city'],
            ['name' => 'Pabna', 'code' => 'PAB', 'country' => 'Bangladesh', 'description' => 'City in northwestern Bangladesh'],
            ['name' => 'Jessore', 'code' => 'JSR', 'country' => 'Bangladesh', 'description' => 'City in southwest Bangladesh'],
            ['name' => 'Noakhali', 'code' => 'NKL', 'country' => 'Bangladesh', 'description' => 'City in southeastern Bangladesh'],
            ['name' => 'Bhola', 'code' => 'BHO', 'country' => 'Bangladesh', 'description' => 'Island city in southern Bangladesh'],
            ['name' => 'Dinajpur', 'code' => 'DNP', 'country' => 'Bangladesh', 'description' => 'City in northwestern Bangladesh'],

            // 🌍 Some other cities
            ['name' => 'Delhi', 'code' => 'DEL', 'country' => 'India', 'description' => 'Capital of India'],
            ['name' => 'Mumbai', 'code' => 'BOM', 'country' => 'India', 'description' => 'Financial capital of India'],
            ['name' => 'Kathmandu', 'code' => 'KTM', 'country' => 'Nepal', 'description' => 'Capital of Nepal'],
            ['name' => 'Thimphu', 'code' => 'THI', 'country' => 'Bhutan', 'description' => 'Capital of Bhutan'],
            ['name' => 'Colombo', 'code' => 'CMB', 'country' => 'Sri Lanka', 'description' => 'Commercial capital of Sri Lanka'],
            ['name' => 'Karachi', 'code' => 'KHI', 'country' => 'Pakistan', 'description' => 'Largest city of Pakistan'],
            ['name' => 'New York', 'code' => 'NYC', 'country' => 'United States', 'description' => 'City in USA'],
            ['name' => 'Toronto', 'code' => 'YYZ', 'country' => 'Canada', 'description' => 'Largest city of Canada'],
            ['name' => 'London', 'code' => 'LON', 'country' => 'United Kingdom', 'description' => 'Capital of the UK'],
            ['name' => 'Sydney', 'code' => 'SYD', 'country' => 'Australia', 'description' => 'City in Australia'],
        ];
        $designations = [
            ['name' => 'Chief Executive Officer (CEO)', 'description' => 'Responsible for overall company direction and leadership.'],
            ['name' => 'Chief Operating Officer (COO)', 'description' => 'Oversees company operations and logistics.'],
            ['name' => 'Chief Technology Officer (CTO)', 'description' => 'Leads technology strategy and development.'],
            ['name' => 'Finance Manager', 'description' => 'Manages financial strategy and reporting.'],
            ['name' => 'HR Manager', 'description' => 'Oversees human resource management.'],
            ['name' => 'Operations Manager', 'description' => 'Supervises day-to-day operations.'],
            ['name' => 'Software Engineer', 'description' => 'Develops software systems and applications.'],
            ['name' => 'Accountant', 'description' => 'Handles financial accounting and reports.'],
            ['name' => 'Sales Executive', 'description' => 'Drives company sales and customer relationships.'],
            ['name' => 'Customer Support Executive', 'description' => 'Provides customer assistance and feedback.'],
        ];
        $purpose = [
            ['name' => 'purpose one', 'description' => 'purpose one','type'=>'Movement Purpose'],
            ['name' => 'purpose one', 'description' => 'purpose one','type'=>'Movement Purpose'],
            ['name' => 'Bus', 'description' => 'Bus','type'=>'Transport Mode'],
            ['name' => 'Car', 'description' => 'Car','type'=>'Transport Mode'],
            ['name' => 'Govement Holaiday', 'description' => 'Govement Holaiday','type'=>'Holiday Type'],
            ['name' => 'Shaheed Day (Intl. Mother Language Day)', 'description' => 'Govement Holaiday','type'=>'Holiday Type'],
            ['name' => 'Birthday of Bangabandhu Sheikh Mujib', 'description' => 'Birthday of Bangabandhu Sheikh Mujib','type'=>'Holiday Type'],
            ['name' => 'Eid-ul-Fitr*', 'description' => 'Eid-ul-Fitr*','type'=>'Holiday Type'],
        ];


        foreach ($cities as $city) {
            InfoMaster::create([
                'type' => 'city',
                'name' => $city['name'],
                'code' => $city['code']??"",
                'description' => $city['description'],
                'parent_id' => $countryIds[$city['country']] ?? null,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($designations as $city) {
            InfoMaster::create([
                'type' => 'designation',
                'name' => $city['name'],
                'code' => $city['code']??"",
                'description' => $city['description']??"",
                'parent_id' => null,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        foreach ($purpose as $city) {
            InfoMaster::create([
                'type' => $city['type']??"",
                'name' => $city['name'],
                'code' => $city['code']??"",
                'description' => $city['description']??"",
                'parent_id' =>  null,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

    }
}
