<?php

namespace App\Services;

use App\Models\Athlete;
use App\Models\Exercise;
use App\Models\Rule;
use Illuminate\Support\Collection;

/**
 * Recommendation engine.
 *
 * Given an athlete's latest measurement per metric and the set of active rules,
 * it (1) evaluates which rules fire, (2) collects the categories they recommend
 * with accumulated weight, and (3) ranks active exercises by that category
 * weight, breaking ties on intensity preference.
 *
 * The pure logic (evaluateRules / scoreExercises) takes plain arrays so it can
 * be unit-tested / simulated without a database.
 */
class RecommendationEngine
{
    /**
     * Full recommendation for an athlete.
     * Returns ['triggered' => [...], 'recommendations' => [...]].
     */
    public function forAthlete(Athlete $athlete): array
    {
        // latest value per metric_id => float
        $latest = $athlete->latestByMetric()
            ->mapWithKeys(fn ($m) => [$m->metric_id => (float) $m->value]);

        $rules = Rule::with('metric')->where('active', true)->get();

        $triggered = $this->evaluateRules($rules, $latest);

        $exercises = Exercise::where('active', true)->get();
        $recommendations = $this->scoreExercises($triggered, $exercises);

        return compact('triggered', 'recommendations');
    }

    /**
     * Evaluate rules against latest values.
     *
     * @param  Collection<Rule>       $rules
     * @param  Collection<int,float>  $latest   metric_id => value
     * @return array<int,array>       list of triggered rule descriptors
     */
    public function evaluateRules(Collection $rules, Collection $latest): array
    {
        $triggered = [];

        foreach ($rules as $rule) {
            // No measurement for this metric → rule can't evaluate.
            if (! $latest->has($rule->metric_id)) {
                continue;
            }

            $value  = (float) $latest->get($rule->metric_id);
            $metric = $rule->metric;
            $min    = $metric?->min_range !== null ? (float) $metric->min_range : null;
            $max    = $metric?->max_range !== null ? (float) $metric->max_range : null;

            if ($this->fires($rule, $value, $min, $max)) {
                $triggered[] = [
                    'rule_id'    => $rule->id,
                    'rule_name'  => $rule->name,
                    'metric'     => $metric?->name,
                    'value'      => $value,
                    'category'   => $rule->recommend_category,
                    'intensity'  => $rule->recommend_intensity,
                    'weight'     => (int) $rule->weight,
                    'reason'     => $rule->describe(),
                ];
            }
        }

        return $triggered;
    }

    /**
     * Decide whether a single rule fires. Pure numeric logic.
     */
    public function fires(Rule $rule, float $value, ?float $min, ?float $max): bool
    {
        return match ($rule->condition) {
            'below'     => $rule->threshold !== null && $value < (float) $rule->threshold,
            'above'     => $rule->threshold !== null && $value > (float) $rule->threshold,
            'below_min' => $min !== null && $value < $min,
            'above_max' => $max !== null && $value > $max,
            'outside'   => ($min !== null && $value < $min) || ($max !== null && $value > $max),
            default     => false,
        };
    }

    /**
     * Rank exercises by accumulated category weight from triggered rules.
     *
     * @param  array<int,array>       $triggered
     * @param  Collection<Exercise>   $exercises
     * @return array<int,array>       ranked recommendations (highest score first)
     */
    public function scoreExercises(array $triggered, Collection $exercises): array
    {
        if (empty($triggered)) {
            return [];
        }

        // Aggregate weight per category, and track preferred intensities.
        $categoryWeight = [];      // category => total weight
        $categoryReasons = [];     // category => [reason strings]
        $categoryIntensity = [];   // category => [intensity => weight]

        foreach ($triggered as $t) {
            $cat = $t['category'];
            $categoryWeight[$cat] = ($categoryWeight[$cat] ?? 0) + $t['weight'];
            $categoryReasons[$cat][] = $t['reason'];
            if ($t['intensity']) {
                $categoryIntensity[$cat][$t['intensity']] =
                    ($categoryIntensity[$cat][$t['intensity']] ?? 0) + $t['weight'];
            }
        }

        $scored = [];
        foreach ($exercises as $ex) {
            $cat = $ex->category;
            if (! $cat || ! isset($categoryWeight[$cat])) {
                continue; // exercise category not recommended by any fired rule
            }

            $score = $categoryWeight[$cat];

            // Tie-break bonus: proportional to the rule weight behind THIS
            // intensity, so a more strongly-preferred intensity ranks higher.
            // Scaled down so it only ever orders within an equal category score,
            // never overtakes a higher-weighted category.
            $intensityBonus = 0.0;
            if (isset($categoryIntensity[$cat][$ex->intensity])) {
                $prefWeight = $categoryIntensity[$cat][$ex->intensity];
                $totalCat   = max($categoryWeight[$cat], 1);
                $intensityBonus = 0.9 * ($prefWeight / $totalCat); // in [0, 0.9)
            }

            $scored[] = [
                'exercise_id' => $ex->id,
                'name'        => $ex->name,
                'category'    => $cat,
                'intensity'   => $ex->intensity,
                'score'       => $score + $intensityBonus,
                'base_score'  => $score,
                'reasons'     => array_values(array_unique($categoryReasons[$cat])),
            ];
        }

        // Sort by score desc, then name asc for stable output.
        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'] ?: strcmp($a['name'], $b['name']);
        });

        return $scored;
    }
}
