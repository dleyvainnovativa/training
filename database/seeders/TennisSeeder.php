<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Metric;
use Illuminate\Database\Seeder;

/**
 * Tennis-specific starter data (metrics + exercises).
 *
 * Opt-in: run with `php artisan db:seed --class=TennisSeeder`.
 * Adds a tennis-tuned set alongside the generic seeds (does not replace them).
 * All rows use firstOrCreate on `name`, so re-running is safe. Categories stay
 * within the shared vocabulary (Resistencia, Fuerza, Velocidad, Potencia,
 * Movilidad) so the recommendation engine keeps matching.
 *
 * Note: some rows overlap conceptually with PadelSeeder but use distinct names,
 * so running both seeders will not collide.
 */
class TennisSeeder extends Seeder
{
    public function run(): void
    {
        $metrics = [
            ['name' => 'VO2 máx (tenis)', 'unit' => 'ml/kg/min', 'category' => 'Resistencia',
             'direction' => 'higher', 'min_range' => 30, 'max_range' => 80,
             'reference' => 'Capacidad aeróbica; sostiene partidos largos y sets prolongados.'],

            ['name' => 'Yo-Yo IR2 (tenis)', 'unit' => 'm', 'category' => 'Resistencia',
             'direction' => 'higher', 'min_range' => 200, 'max_range' => 2000,
             'reference' => 'Resistencia intermitente de alta intensidad, propia del intercambio largo.'],

            ['name' => 'Sprint 20 m', 'unit' => 's', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 2.5, 'max_range' => 5,
             'reference' => 'Aceleración en distancia media; cobertura de cancha completa.'],

            ['name' => 'Agilidad en araña (spider drill)', 'unit' => 's', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 12, 'max_range' => 25,
             'reference' => 'Circuito de toques a las esquinas de la cancha; desplazamiento multidireccional.'],

            ['name' => 'Velocidad de saque (radar)', 'unit' => 'km/h', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 100, 'max_range' => 220,
             'reference' => 'Potencia y coordinación de la cadena cinética en el saque.'],

            ['name' => 'Salto vertical (CMJ)', 'unit' => 'cm', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 25, 'max_range' => 75,
             'reference' => 'Potencia de tren inferior; salto en saque y smash.'],

            ['name' => 'Lanzamiento rotacional de balón medicinal', 'unit' => 'm', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 3, 'max_range' => 15,
             'reference' => 'Potencia rotacional del core para golpes de fondo.'],

            ['name' => 'Sentadilla 1RM', 'unit' => 'kg', 'category' => 'Fuerza',
             'direction' => 'higher', 'min_range' => 30, 'max_range' => 250,
             'reference' => 'Fuerza máxima de tren inferior, base para potencia y frenado.'],

            ['name' => 'Dominadas (repeticiones)', 'unit' => 'reps', 'category' => 'Fuerza',
             'direction' => 'higher', 'min_range' => 0, 'max_range' => 30,
             'reference' => 'Fuerza de tracción de tren superior; equilibrio para el hombro.'],

            ['name' => 'Rotación interna/externa de hombro', 'unit' => '°', 'category' => 'Movilidad',
             'direction' => 'higher', 'min_range' => 90, 'max_range' => 180,
             'reference' => 'Rango del manguito rotador; prevención en el brazo de saque.'],

            ['name' => 'Movilidad torácica (rotación)', 'unit' => '°', 'category' => 'Movilidad',
             'direction' => 'higher', 'min_range' => 30, 'max_range' => 90,
             'reference' => 'Rotación torácica para golpes potentes y seguros de fondo.'],
        ];

        foreach ($metrics as $m) {
            Metric::firstOrCreate(['name' => $m['name']], $m);
        }

        $exercises = [
            // Resistencia
            ['name' => 'Peloteo de fondo prolongado', 'category' => 'Resistencia', 'intensity' => 'high',
             'equipment' => 'Cancha de tenis', 'tags' => ['intermitente', 'específico'],
             'description' => 'Intercambios largos de fondo con desplazamiento continuo.'],
            ['name' => 'Carrera continua 30-40 min', 'category' => 'Resistencia', 'intensity' => 'medium',
             'equipment' => 'Ninguno', 'tags' => ['aeróbico', 'base'],
             'description' => 'Base aeróbica para sostener partidos de larga duración.'],
            ['name' => 'Fartlek en cancha', 'category' => 'Resistencia', 'intensity' => 'high',
             'equipment' => 'Conos', 'tags' => ['intervalos', 'variable'],
             'description' => 'Cambios de ritmo que imitan la intermitencia del juego.'],

            // Fuerza
            ['name' => 'Sentadilla con barra (tenis)', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Barra', 'tags' => ['tren inferior', 'base'],
             'description' => 'Fuerza máxima de piernas; base de potencia y frenado.'],
            ['name' => 'Peso muerto rumano', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Barra', 'tags' => ['cadena posterior', 'isquios'],
             'description' => 'Fuerza de cadena posterior; protege isquios en la aceleración.'],
            ['name' => 'Dominadas', 'category' => 'Fuerza', 'intensity' => 'medium',
             'equipment' => 'Barra fija', 'tags' => ['tracción', 'espalda'],
             'description' => 'Equilibra la musculatura del hombro del brazo ejecutor.'],
            ['name' => 'Press militar', 'category' => 'Fuerza', 'intensity' => 'medium',
             'equipment' => 'Barra', 'tags' => ['hombro', 'empuje vertical'],
             'description' => 'Empuje vertical que apoya la mecánica del saque.'],

            // Velocidad
            ['name' => 'Sprints laterales de línea a línea', 'category' => 'Velocidad', 'intensity' => 'high',
             'equipment' => 'Conos', 'tags' => ['lateral', 'cobertura'],
             'description' => 'Desplazamientos rápidos a lo ancho de la cancha con frenado.'],
            ['name' => 'Spider drill (toques a esquinas)', 'category' => 'Velocidad', 'intensity' => 'high',
             'equipment' => 'Pelotas', 'tags' => ['multidireccional', 'específico'],
             'description' => 'Sprints cortos a las cinco marcas de la cancha.'],
            ['name' => 'Escalera de agilidad (tenis)', 'category' => 'Velocidad', 'intensity' => 'medium',
             'equipment' => 'Escalera', 'tags' => ['pies rápidos', 'coordinación'],
             'description' => 'Coordinación y frecuencia de pies para el ajuste fino.'],
            ['name' => 'Split-step reactivo', 'category' => 'Velocidad', 'intensity' => 'medium',
             'equipment' => 'Ninguno', 'tags' => ['reacción', 'específico'],
             'description' => 'Salto de preparación y salida explosiva ante señal.'],

            // Potencia
            ['name' => 'Salto al cajón (tenis)', 'category' => 'Potencia', 'intensity' => 'high',
             'equipment' => 'Cajón', 'tags' => ['pliometría', 'tren inferior'],
             'description' => 'Potencia vertical para saque y smash.'],
            ['name' => 'Lanzamiento rotacional de balón medicinal', 'category' => 'Potencia', 'intensity' => 'high',
             'equipment' => 'Balón medicinal', 'tags' => ['rotación', 'core'],
             'description' => 'Transferencia de potencia rotacional a los golpes de fondo.'],
            ['name' => 'Saque con banda de resistencia', 'category' => 'Potencia', 'intensity' => 'medium',
             'equipment' => 'Banda elástica', 'tags' => ['hombro', 'saque'],
             'description' => 'Gesto de saque resistido para potencia específica.'],
            ['name' => 'Zancada con salto', 'category' => 'Potencia', 'intensity' => 'medium',
             'equipment' => 'Ninguno', 'tags' => ['pliometría', 'unilateral'],
             'description' => 'Potencia unilateral aplicada a salidas y recuperación.'],

            // Movilidad
            ['name' => 'Movilidad de hombro con banda', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Banda elástica', 'tags' => ['hombro', 'manguito'],
             'description' => 'Salud y rango del manguito rotador para el saque.'],
            ['name' => 'Rotaciones torácicas', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Ninguno', 'tags' => ['torácica', 'rotación'],
             'description' => 'Rotación de la columna torácica para golpes potentes y seguros.'],
            ['name' => 'Movilidad de cadera (90/90)', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Ninguno', 'tags' => ['cadera', 'rango'],
             'description' => 'Rango de cadera para apoyos amplios y estables.'],
        ];

        foreach ($exercises as $e) {
            Exercise::firstOrCreate(['name' => $e['name']], $e);
        }
    }
}
