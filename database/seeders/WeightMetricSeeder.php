<?php

namespace Database\Seeders;

use App\Models\Metric;
use Illuminate\Database\Seeder;

/**
 * Adds the body-weight metric so weight can be tracked over time via the normal
 * measurement + history-chart flow (and feed BMI on the athlete profile).
 *
 * Opt-in: run with `php artisan db:seed --class=WeightMetricSeeder`.
 * The metric name must stay in sync with Athlete::WEIGHT_METRIC_NAMES for BMI
 * to pick it up. Category is left null on purpose: body weight is descriptive,
 * not one of the five engine categories, so no recommendation rule keys off it.
 */
class WeightMetricSeeder extends Seeder
{
    public function run(): void
    {
        Metric::firstOrCreate(
            ['name' => 'Peso corporal'],
            [
                'name'      => 'Peso corporal',
                'unit'      => 'kg',
                'category'  => null,
                'direction' => 'lower', // neutral; weight isn't "better" either way
                'min_range' => 30,
                'max_range' => 200,
                'reference' => 'Peso corporal. Se registra en el tiempo y alimenta el IMC del perfil.',
            ]
        );
    }
}
