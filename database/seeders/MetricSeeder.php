<?php

namespace Database\Seeders;

use App\Models\Metric;
use Illuminate\Database\Seeder;

/**
 * Example metrics using real sports-science standards. The manager can edit or
 * delete these — they're just a useful starting point, not fixed.
 */
class MetricSeeder extends Seeder
{
    public function run(): void
    {
        $metrics = [
            ['name' => 'VO2 máx', 'unit' => 'ml/kg/min', 'category' => 'Resistencia',
             'direction' => 'higher', 'min_range' => 20, 'max_range' => 90,
             'reference' => 'Consumo máximo de oxígeno. Mayor indica mejor capacidad aeróbica.'],

            ['name' => 'Frecuencia cardíaca en reposo', 'unit' => 'bpm', 'category' => 'Resistencia',
             'direction' => 'lower', 'min_range' => 30, 'max_range' => 100,
             'reference' => 'Menor suele indicar mejor condición cardiovascular.'],

            ['name' => 'Sprint 40 m', 'unit' => 's', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 4, 'max_range' => 8,
             'reference' => 'Tiempo en 40 metros. Menor es mejor.'],

            ['name' => 'Salto vertical', 'unit' => 'cm', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 20, 'max_range' => 100,
             'reference' => 'Altura de salto. Mayor indica más potencia de piernas.'],

            ['name' => '1RM Sentadilla', 'unit' => 'kg', 'category' => 'Fuerza',
             'direction' => 'higher', 'min_range' => 20, 'max_range' => 300,
             'reference' => 'Una repetición máxima en sentadilla.'],

            ['name' => 'Flexibilidad (sit & reach)', 'unit' => 'cm', 'category' => 'Movilidad',
             'direction' => 'higher', 'min_range' => -20, 'max_range' => 40,
             'reference' => 'Prueba de flexión de tronco. Mayor alcance es mejor.'],
        ];

        foreach ($metrics as $m) {
            Metric::firstOrCreate(['name' => $m['name']], $m);
        }
    }
}
