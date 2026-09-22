<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

/**
 * Starter exercise catalog spanning every category so the Tier 4 engine has
 * something to match against out of the box. Fully editable by the manager.
 */
class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Resistencia
            ['name' => 'Carrera continua', 'category' => 'Resistencia', 'intensity' => 'medium',
             'equipment' => 'Ninguno', 'tags' => ['aeróbico', 'base'],
             'description' => 'Trote sostenido a ritmo constante para base aeróbica.'],
            ['name' => 'Intervalos 400 m', 'category' => 'Resistencia', 'intensity' => 'high',
             'equipment' => 'Pista', 'tags' => ['VO2', 'intervalos'],
             'description' => 'Series de 400 m con recuperación; mejora consumo de oxígeno.'],

            // Fuerza
            ['name' => 'Sentadilla con barra', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Barra', 'tags' => ['tren inferior', 'cuádriceps'],
             'description' => 'Ejercicio base de fuerza de piernas.'],
            ['name' => 'Peso muerto', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Barra', 'tags' => ['cadena posterior', 'espalda baja']],

            // Velocidad
            ['name' => 'Sprints 30 m', 'category' => 'Velocidad', 'intensity' => 'high',
             'equipment' => 'Ninguno', 'tags' => ['aceleración', 'explosivo']],
            ['name' => 'Escalera de agilidad', 'category' => 'Velocidad', 'intensity' => 'medium',
             'equipment' => 'Escalera', 'tags' => ['coordinación', 'pies rápidos']],

            // Potencia
            ['name' => 'Salto al cajón', 'category' => 'Potencia', 'intensity' => 'high',
             'equipment' => 'Cajón', 'tags' => ['pliometría', 'tren inferior']],
            ['name' => 'Lanzamiento de balón medicinal', 'category' => 'Potencia', 'intensity' => 'medium',
             'equipment' => 'Balón medicinal', 'tags' => ['core', 'explosivo']],

            // Movilidad
            ['name' => 'Movilidad de cadera', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Ninguno', 'tags' => ['cadera', 'rango']],
            ['name' => 'Estiramiento isquiotibiales', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Ninguno', 'tags' => ['flexibilidad', 'posterior']],
        ];

        foreach ($exercises as $e) {
            Exercise::firstOrCreate(['name' => $e['name']], $e);
        }
    }
}
