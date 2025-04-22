<?php

namespace Database\Seeders;

use App\Enums\FuelType;
use App\Enums\RefuelingType;
use App\Models\Aircraft;
use App\Models\Department;
use App\Models\GasStation;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create AVGAS Station with demo filling
        $avgas = GasStation::create([
            'name' => 'AVGAS',
            'fuel_type' => FuelType::avgas,
            'capacity' => 10000,
        ]);
        $avgas->refuelings()->create([
            'date' => today()->subDays(14),
            'type' => RefuelingType::filling,
            'buyer_name' => 'John Doe',
            'counter_reading' => 180440,
            'amount' => 10000,
        ]);

        // Create Super Station with demo filling
        $super = GasStation::create([
            'name' => 'Super',
            'fuel_type' => FuelType::super,
            'capacity' => 800,
        ]);
        $super->refuelings()->create([
            'date' => today()->subDays(14),
            'type' => RefuelingType::filling,
            'buyer_name' => 'John Doe',
            'counter_reading' => 25440,
            'amount' => 800,
        ]);

        // Create some Aircraft
        Aircraft::factory()->times(10)->create();

        // Create departments
        $departments = [
            'Außenanlagen',
            'Checklisten',
            'Clubheim',
            'Events / Veranstaltungen',
            'Fahrzeuge / Fuhrpark',
            'Interieur / Nähteam',
            'IT',
            'Neumitglieder',
            'Öffentlichkeitsarbeit',
            'Piste / Flugbetriebsfläche',
        ];

        foreach ($departments as $department) {
            Department::factory()->create([
                'name' => $department,
            ]);
        }
    }
}
