<?php

namespace App\Services;

use App\Models\Athlete;
use App\Models\Routine;

/**
 * Builds routine items from the recommendation engine's ranked output.
 *
 * Distribution strategy: take the top-N recommended exercises and spread them
 * round-robin across the chosen training days, so each day gets a balanced mix
 * rather than all the top exercises landing on Monday. Higher-ranked exercises
 * are placed first (earlier days, earlier positions).
 */
class RoutineBuilder
{
    public function __construct(
        private readonly RecommendationEngine $engine
    ) {}

    /**
     * Generate day => [items] for an athlete.
     *
     * @param  int[]  $trainingDays  ISO days to fill, e.g. [1,3,5]
     * @param  int    $perDay        target exercises per day
     * @return array<int,array<int,array>>  day_of_week => list of item payloads
     */
    public function buildForAthlete(Athlete $athlete, array $trainingDays, int $perDay = 3): array
    {
        $recs = $this->engine->forAthlete($athlete)['recommendations'];

        return $this->distribute($recs, $trainingDays, $perDay);
    }

    /**
     * Pure distribution logic — no DB, so it can be simulated/tested.
     *
     * @param  array<int,array>  $recs          ranked recommendations
     * @param  int[]             $trainingDays
     * @param  int               $perDay
     * @return array<int,array<int,array>>      day => positioned items
     */
    public function distribute(array $recs, array $trainingDays, int $perDay): array
    {
        $days = array_values(array_filter($trainingDays, fn ($d) => $d >= 1 && $d <= 7));
        if (empty($days) || $perDay < 1 || empty($recs)) {
            return [];
        }

        $capacity = count($days) * $perDay;
        $selected = array_slice($recs, 0, $capacity);

        $plan = array_fill_keys($days, []);
        $dayCount = count($days);

        foreach ($selected as $i => $rec) {
            $day = $days[$i % $dayCount];       // round-robin across days
            $plan[$day][] = [
                'exercise_id' => $rec['exercise_id'],
                'position'    => count($plan[$day]),
                'prescription' => null,
            ];
        }

        // Drop any days that ended up empty (e.g. fewer recs than days).
        return array_filter($plan, fn ($items) => ! empty($items));
    }
}
