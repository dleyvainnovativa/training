<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Metric;
use Illuminate\Database\Seeder;

/**
 * Padel-specific starter data (metrics + exercises).
 *
 * Opt-in: run with `php artisan db:seed --class=PadelSeeder`.
 * It does NOT replace MetricSeeder / ExerciseSeeder — it adds a padel-tuned
 * set alongside them. All rows use firstOrCreate on `name`, so re-running is
 * safe and won't duplicate. Categories stay within the shared vocabulary
 * (Resistencia, Fuerza, Velocidad, Potencia, Movilidad) so the engine matches.
 */
class PadelSeeder extends Seeder
{
    public function run(): void
    {
        $metrics = [
            ['name' => 'Test de Course-Navette (Yo-Yo IR1)', 'unit' => 'm', 'category' => 'Resistencia',
             'direction' => 'higher', 'min_range' => 200, 'max_range' => 2400,
             'reference' => 'Distancia en el Yo-Yo Intermittent Recovery. Refleja la resistencia intermitente propia del pádel.'],

            ['name' => 'Sprint lateral 5-10-5 (agilidad)', 'unit' => 's', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 4, 'max_range' => 8,
             'reference' => 'Prueba pro-agility de cambios de dirección. Menor tiempo = mejor desplazamiento lateral en cancha.'],

            ['name' => 'Sprint 10 m', 'unit' => 's', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 1.5, 'max_range' => 3,
             'reference' => 'Aceleración corta hacia la red. En pádel priman los primeros metros, no la velocidad máxima.'],

            ['name' => 'Salto horizontal (broad jump)', 'unit' => 'cm', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 100, 'max_range' => 300,
             'reference' => 'Salto de longitud sin impulso. Potencia horizontal para salidas y remates.'],

            ['name' => 'Lanzamiento de balón medicinal (pecho)', 'unit' => 'm', 'category' => 'Potencia',
             'direction' => 'higher', 'min_range' => 2, 'max_range' => 10,
             'reference' => 'Potencia del tren superior aplicada a la bandeja y el remate.'],

            ['name' => 'Press de banca 1RM', 'unit' => 'kg', 'category' => 'Fuerza',
             'direction' => 'higher', 'min_range' => 20, 'max_range' => 150,
             'reference' => 'Fuerza del tren superior (empuje) para golpes de potencia.'],

            ['name' => 'Sentadilla búlgara 6RM', 'unit' => 'kg', 'category' => 'Fuerza',
             'direction' => 'higher', 'min_range' => 5, 'max_range' => 80,
             'reference' => 'Fuerza unilateral de pierna; clave por los apoyos asimétricos en cancha.'],

            ['name' => 'Tiempo de reacción visual', 'unit' => 'ms', 'category' => 'Velocidad',
             'direction' => 'lower', 'min_range' => 150, 'max_range' => 500,
             'reference' => 'Respuesta a un estímulo visual. Relevante para la volea y el juego en red.'],

            ['name' => 'Rotación de hombro (movilidad)', 'unit' => '°', 'category' => 'Movilidad',
             'direction' => 'higher', 'min_range' => 90, 'max_range' => 180,
             'reference' => 'Rango de rotación glenohumeral; protege el hombro del brazo ejecutor.'],

            ['name' => 'Sentadilla profunda (movilidad de tobillo)', 'unit' => 'cm', 'category' => 'Movilidad',
             'direction' => 'higher', 'min_range' => 0, 'max_range' => 20,
             'reference' => 'Distancia rodilla-pared. Buena movilidad de tobillo mejora los apoyos bajos.'],
        ];

        foreach ($metrics as $m) {
            Metric::firstOrCreate(['name' => $m['name']], $m);
        }

        $exercises = [
            // Resistencia
            ['name' => 'Peloteo de resistencia (intervalos en cancha)', 'category' => 'Resistencia', 'intensity' => 'high',
             'equipment' => 'Pista de pádel', 'tags' => ['intermitente', 'específico'],
             'description' => 'Series de peloteo intenso con pausas cortas, replicando la demanda del partido.'],
            ['name' => 'Yo-Yo / course-navette', 'category' => 'Resistencia', 'intensity' => 'high',
             'equipment' => 'Conos', 'tags' => ['aeróbico', 'intermitente'],
             'description' => 'Carreras de ida y vuelta a ritmo creciente para la resistencia intermitente.'],
            ['name' => 'Bici o remo continuo', 'category' => 'Resistencia', 'intensity' => 'medium',
             'equipment' => 'Bici/Remo', 'tags' => ['base', 'bajo impacto'],
             'description' => 'Trabajo aeróbico de base con bajo impacto articular.'],

            // Fuerza
            ['name' => 'Sentadilla búlgara', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Mancuernas', 'tags' => ['unilateral', 'tren inferior'],
             'description' => 'Fuerza de pierna unilateral para los apoyos asimétricos.'],
            ['name' => 'Press de banca', 'category' => 'Fuerza', 'intensity' => 'high',
             'equipment' => 'Barra', 'tags' => ['empuje', 'tren superior'],
             'description' => 'Empuje horizontal para potencia de remate y bandeja.'],
            ['name' => 'Remo con barra', 'category' => 'Fuerza', 'intensity' => 'medium',
             'equipment' => 'Barra', 'tags' => ['tracción', 'espalda'],
             'description' => 'Equilibra el empuje y protege el hombro.'],
            ['name' => 'Antebrazo y muñeca (curl de muñeca)', 'category' => 'Fuerza', 'intensity' => 'low',
             'equipment' => 'Mancuerna', 'tags' => ['muñeca', 'agarre'],
             'description' => 'Fortalece muñeca y antebrazo para el control de la pala.'],

            // Velocidad
            ['name' => 'Desplazamientos laterales en cancha', 'category' => 'Velocidad', 'intensity' => 'high',
             'equipment' => 'Conos', 'tags' => ['lateral', 'cambio de dirección'],
             'description' => 'Sprints laterales cortos con frenado y reaceleración.'],
            ['name' => 'Escalera de agilidad (pádel)', 'category' => 'Velocidad', 'intensity' => 'medium',
             'equipment' => 'Escalera', 'tags' => ['pies rápidos', 'coordinación'],
             'description' => 'Patrones de pies rápidos para la coordinación en desplazamiento.'],
            ['name' => 'Salidas cortas 5-10 m', 'category' => 'Velocidad', 'intensity' => 'high',
             'equipment' => 'Ninguno', 'tags' => ['aceleración', 'primer paso'],
             'description' => 'Aceleraciones cortas hacia la red; prioriza el primer paso.'],
            ['name' => 'Reacción con estímulo visual', 'category' => 'Velocidad', 'intensity' => 'medium',
             'equipment' => 'Ninguno', 'tags' => ['reacción', 'volea'],
             'description' => 'Salidas o toques respondiendo a una señal visual imprevista.'],

            // Potencia
            ['name' => 'Salto lateral con caja', 'category' => 'Potencia', 'intensity' => 'high',
             'equipment' => 'Cajón', 'tags' => ['pliometría', 'lateral'],
             'description' => 'Saltos laterales explosivos que transfieren al juego en cancha.'],
            ['name' => 'Lanzamiento de balón medicinal rotacional', 'category' => 'Potencia', 'intensity' => 'medium',
             'equipment' => 'Balón medicinal', 'tags' => ['rotación', 'core'],
             'description' => 'Potencia rotacional del core para golpes de derecha y revés.'],
            ['name' => 'Remate/bandeja con banda elástica', 'category' => 'Potencia', 'intensity' => 'medium',
             'equipment' => 'Banda elástica', 'tags' => ['hombro', 'específico'],
             'description' => 'Gesto de remate resistido para potencia específica del golpe.'],

            // Movilidad
            ['name' => 'Movilidad de hombro (dislocates con banda)', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Banda elástica', 'tags' => ['hombro', 'prevención'],
             'description' => 'Rango y salud del hombro del brazo ejecutor.'],
            ['name' => 'Movilidad de cadera y tobillo', 'category' => 'Movilidad', 'intensity' => 'low',
             'equipment' => 'Ninguno', 'tags' => ['cadera', 'tobillo'],
             'description' => 'Apoyos bajos y cambios de dirección más seguros.'],
        ];

        foreach ($exercises as $e) {
            Exercise::firstOrCreate(['name' => $e['name']], $e);
        }
    }
}
